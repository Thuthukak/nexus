<?php

declare(strict_types=1);

namespace Modules\Financial\app\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Financial\app\Models\Invoice;
use Modules\Financial\app\Models\InvoiceLine;
use Modules\Financial\app\Models\Payment;
use Illuminate\Support\Facades\Log;

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
        return $invoice->fresh();
    }

    public function markSent(Invoice $invoice): Invoice
    {
        abort_if(
            ! in_array($invoice->status, ['approved', 'draft']),
            422,
            'Invoice cannot be marked as sent.'
        );

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
            $lineTotal = round($line['qty'] * $line['unit_price'], 2);
            InvoiceLine::create([
                'invoice_id'  => $invoice->id,
                'description' => $line['description'],
                'qty'         => $line['qty'],
                'unit_price'  => $line['unit_price'],
                'tax_rate'    => $line['tax_rate'] ?? 0,
                'line_total'  => $lineTotal,
                'sort_order'  => $i,
            ]);
        }
    }

    private function nextReference(): string
    {
        $prefix = config('financial.invoice_prefix', 'INV-');
        $last   = Invoice::withTrashed()
                        ->orderByDesc('created_at')
                        ->value('reference');

        if (! $last) return $prefix . '0001';

        $number = (int) str_replace($prefix, '', $last);
        return $prefix . str_pad((string) ($number + 1), 4, '0', STR_PAD_LEFT);
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