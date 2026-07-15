<?php

declare(strict_types=1);

namespace Modules\Financial\app\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Financial\app\Events\InvoiceApproved;
use App\Services\ActivityLogService;
use Modules\Financial\app\Events\InvoicePaid;
use Modules\Financial\app\Events\InvoiceOverdue;
use Modules\Financial\app\Models\Invoice;
use Modules\Financial\app\Models\InvoiceLine;
use Modules\Financial\app\Models\Payment;

class InvoiceService
{
    public function create(array $data, int $userId): Invoice
    {
        return DB::transaction(function () use ($data, $userId) {
            $invoice = Invoice::create([
                'reference'   => $this->nextReference(),
                'customer_id' => $data['customer_id'],
                'created_by'  => $userId,
                'status'      => 'draft',
                'issue_date'  => $data['issue_date'] ?? today(),
                'due_date'    => $data['due_date'],
                'currency'    => $data['currency'] ?? config('financial.currency', 'ZAR'),
                'notes'              => $data['notes'] ?? null,
                'deposit_required'   => $data['deposit_required'] ?? false,
                'deposit_type'       => $data['deposit_type'] ?? 'percentage',
                'deposit_percentage' => $data['deposit_percentage'] ?? 50,
                'deposit_amount'     => $data['deposit_amount'] ?? 0,
            ]);

            $this->syncLines($invoice, $data['lines'] ?? []);
            $invoice->recalculate();

            return $invoice->fresh(['lines', 'customer']);
        });
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            $invoice->update([
                'customer_id' => $data['customer_id'],
                'due_date'    => $data['due_date'],
                'issue_date'  => $data['issue_date'] ?? $invoice->issue_date,
                'notes'       => $data['notes'] ?? null,
            ]);

            $this->syncLines($invoice, $data['lines'] ?? []);
            $invoice->recalculate();
            app(ActivityLogService::class)->log($invoice, 'Invoice updated', [], 'invoice');
            return $invoice->fresh(['lines', 'customer']);
        });
    }

    public function approve(Invoice $invoice): Invoice
    {
        abort_if(
            ! in_array($invoice->status, ['draft']),
            422,
            'Only draft invoices can be approved.'
        );

        $invoice->update(['status' => 'approved']);
        app(ActivityLogService::class)->logStatusChange($invoice, 'draft', 'approved', 'Invoice approved');
        event(new InvoiceApproved($invoice->fresh()));
        return $invoice->fresh();
    }

    public function markSent(Invoice $invoice): Invoice
    {
        abort_if(
            ! in_array($invoice->status, ['approved', 'draft']),
            422,
            'Invoice cannot be marked as sent.'
        );
    
            app(ActivityLogService::class)->logStatusChange($invoice, 'draft', 'sent', 'Invoice marked as sent');
            $invoice->update(['status' => 'sent']);
            return $invoice->fresh();
        }

    public function recordPayment(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'amount'     => $data['amount'],
                'method'     => $data['method'],
                'reference'  => $data['reference'] ?? null,
                'notes'      => $data['notes']     ?? null,
                'paid_at'    => $data['paid_at']   ?? now(),
            ]);

            $totalPaid = Payment::where('invoice_id', $invoice->id)->sum('amount');
            $invoice->update(['paid_total' => $totalPaid]);

            // Determine new status
            $depositPaid = $invoice->deposit_required && $totalPaid >= $invoice->deposit_amount;
            $fullyPaid   = $totalPaid >= $invoice->total;

            $status = match (true) {
                $fullyPaid  => 'paid',
                $depositPaid => 'deposit_paid',
                $totalPaid > 0 => 'part_paid',
                default     => $invoice->status,
            };

            $updates = ['status' => $status];
            if ($depositPaid && ! $invoice->deposit_paid_at) {
                $updates['deposit_paid_at'] = now();
            }

            $invoice->update($updates);

            if ($status === 'paid') {
                event(new InvoicePaid($invoice->fresh()));
            }

            return $payment;
        });
    }

    public function cancel(Invoice $invoice): Invoice
    {
        abort_if(
            in_array($invoice->status, ['paid', 'cancelled']),
            422,
            'This invoice cannot be cancelled.'
        );

        app(ActivityLogService::class)->logStatusChange($invoice, $invoice->status, 'cancelled', 'Invoice cancelled');
        $invoice->update(['status' => 'cancelled']);
        return $invoice->fresh();
    }

    public function duplicate(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            $invoice->load('lines');

            $newInvoice = Invoice::create([
                'reference'   => $this->nextReference(),
                'customer_id' => $invoice->customer_id,
                'created_by'  => auth()->id(),
                'status'      => 'draft',
                'issue_date'  => today(),
                'due_date'    => today()->addDays(30),
                'currency'    => $invoice->currency,
                'notes'       => $invoice->notes,
            ]);

            $this->syncLines($newInvoice, $invoice->lines->map(fn ($l) => [
                'description' => $l->description,
                'qty'         => $l->qty,
                'unit_price'  => $l->unit_price,
                'tax_rate'    => $l->tax_rate,
            ])->toArray());

            $newInvoice->recalculate();

            return $newInvoice->fresh(['lines', 'customer']);
        });
    }

    private function syncLines(Invoice $invoice, array $lines): void
    {
        $invoice->lines()->delete();

        foreach ($lines as $i => $line) {
            // line_total is always qty × unit_price (the entered price).
            // Whether that price is tax-inclusive or exclusive is tracked
            // separately — the calculation is done in InvoiceLine::taxAmount().
            $lineTotal = round((float) $line['qty'] * (float) $line['unit_price'], 2);

            InvoiceLine::create([
                'invoice_id'      => $invoice->id,
                'description'     => $line['description'],
                'qty'             => $line['qty'],
                'unit_price'      => $line['unit_price'],
                'tax_rate'        => $line['tax_rate'] ?? 0,
                'is_tax_inclusive'=> $line['is_tax_inclusive'] ?? true,
                'line_total'      => $lineTotal,
                'sort_order'      => $i,
            ]);
        }
    }

    /**
     * Generates the next sequential reference for the current MM-YYYY period,
     * e.g. INV-07-2026-0001. Must be called from within a DB::transaction()
     * so the row lock actually protects against concurrent inserts.
     */
    private function nextReference(): string
    {
        $prefix = config('financial.invoice_prefix');
        $month  = now()->format('m');
        $year   = now()->format('Y');

        $currentPrefix = "{$prefix}{$year}-{$month}-";

        $last = Invoice::withTrashed()
                        ->where('reference', 'like', $currentPrefix . '%')
                        ->orderByDesc('reference')
                        ->lockForUpdate()
                        ->value('reference');

        $number = $last
            ? (int) str_replace($currentPrefix, '', $last)
            : 0;

        return $currentPrefix . str_pad((string) ($number + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Retries an invoice-creating callback if it fails due to a unique
     * constraint violation on `reference` (belt-and-braces alongside the
     * row lock in nextReference — protects against edge cases where the
     * lock scope doesn't fully serialize, e.g. no existing row to lock on
     * the very first invoice of a new month under concurrent requests).
     */
    private function withUniqueRetry(callable $callback, int $maxAttempts = 5)
    {
        $attempts = 0;

        while (true) {
            $attempts++;

            try {
                return $callback();
            } catch (QueryException $e) {
                $isUniqueViolation = in_array($e->getCode(), ['23000', '23505'], true);

                if ($isUniqueViolation && $attempts < $maxAttempts) {
                    usleep(50_000 * $attempts);
                    continue;
                }

                throw $e;
            }
        }
    }

    public function queueSend(Invoice $invoice): void
    {
        abort_if(
            ! in_array($invoice->status, ['draft', 'approved']),
            422,
            'Only draft or approved invoices can be sent.'
        );

        abort_if(
            ! $invoice->customer->email,
            422,
            'This customer has no email address. Please update the customer record first.'
        );

        // Dispatch the job to the queue
        \App\Jobs\SendInvoiceJob::dispatch($invoice->id);

        // Optimistically mark as queued so UI reflects intent immediately
        $invoice->update(['status' => 'sent', 'last_sent_at' => now()]);
    }
}