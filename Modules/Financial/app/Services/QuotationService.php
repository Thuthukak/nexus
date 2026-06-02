<?php

declare(strict_types=1);

namespace Modules\Financial\app\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Financial\app\Models\Invoice;
use Modules\Financial\app\Models\InvoiceLine;
use Modules\Financial\app\Models\Quotation;
use Modules\Financial\app\Models\QuotationLine;

class QuotationService
{
    public function create(array $data, int $userId): Quotation
    {
        return DB::transaction(function () use ($data, $userId) {
            $quotation = Quotation::create([
                'reference'   => $this->nextReference(),
                'customer_id' => $data['customer_id'],
                'created_by'  => $userId,
                'status'      => 'draft',
                'issue_date'  => $data['issue_date'] ?? today(),
                'valid_until' => $data['valid_until'],
                'net_terms'   => $data['net_terms'] ?? 'custom',
                'currency'    => $data['currency'] ?? config('financial.currency', 'ZAR'),
                'notes'       => $data['notes'] ?? null,
                'terms'       => $data['terms'] ?? null,
            ]);

            $this->syncLines($quotation, $data['lines'] ?? []);
            $quotation->load('lines');
            $quotation->recalculate();

            return $quotation->fresh(['lines', 'customer']);
        });
    }

    public function update(Quotation $quotation, array $data): Quotation
    {
        return DB::transaction(function () use ($quotation, $data) {
            $quotation->update([
                'customer_id' => $data['customer_id'],
                'valid_until' => $data['valid_until'],
                'issue_date'  => $data['issue_date'] ?? $quotation->issue_date,
                'net_terms'   => $data['net_terms'] ?? $quotation->net_terms,
                'notes'       => $data['notes'] ?? null,
                'terms'       => $data['terms'] ?? null,
            ]);

            $this->syncLines($quotation, $data['lines'] ?? []);
            $quotation->load('lines');
            $quotation->recalculate();

            return $quotation->fresh(['lines', 'customer']);
        });
    }

    public function send(Quotation $quotation): Quotation
    {
        abort_if(
            ! in_array($quotation->status, ['draft']),
            422, 'Only draft quotations can be sent.'
        );

        abort_if(
            ! $quotation->customer->email,
            422, 'This customer has no email address.'
        );

        $quotation->generateToken();
        $quotation->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);

        \App\Jobs\SendQuotationJob::dispatch($quotation->id);

        return $quotation->fresh();
    }

    public function accept(Quotation $quotation): Quotation
    {
        abort_if(
            $quotation->status !== 'sent',
            422, 'Only sent quotations can be accepted.'
        );

        $quotation->update([
            'status'      => 'accepted',
            'accepted_at' => now(),
        ]);

        return $quotation->fresh();
    }

    public function decline(Quotation $quotation): Quotation
    {
        abort_if(
            $quotation->status !== 'sent',
            422, 'Only sent quotations can be declined.'
        );

        $quotation->update([
            'status'      => 'declined',
            'declined_at' => now(),
        ]);

        return $quotation->fresh();
    }

    public function convertToInvoice(Quotation $quotation): Invoice
    {
        abort_if(
            $quotation->status !== 'accepted',
            422, 'Only accepted quotations can be converted to invoices.'
        );

        return DB::transaction(function () use ($quotation) {
            $quotation->load('lines');

            // Build invoice reference
            $invoiceService = app(InvoiceService::class);

            $invoice = $invoiceService->create([
                'customer_id' => $quotation->customer_id,
                'issue_date'  => today()->format('Y-m-d'),
                'due_date'    => today()->addDays(30)->format('Y-m-d'),
                'currency'    => $quotation->currency,
                'notes'       => $quotation->notes,
                'lines'       => $quotation->lines->map(fn ($l) => [
                    'description' => $l->description,
                    'qty'         => $l->qty,
                    'unit_price'  => $l->unit_price,
                    'tax_rate'    => $l->tax_rate,
                ])->toArray(),
            ], $quotation->created_by);

            $quotation->update([
                'status'               => 'converted',
                'converted_invoice_id' => $invoice->id,
            ]);

            return $invoice;
        });
    }

    public function markExpired(): int
    {
        $count = 0;
        Quotation::where('status', 'sent')
            ->where('valid_until', '<', today())
            ->each(function ($q) use (&$count) {
                $q->update(['status' => 'expired']);
                $count++;
            });
        return $count;
    }

    private function syncLines(Quotation $quotation, array $lines): void
    {
        $quotation->lines()->delete();
        foreach ($lines as $i => $line) {
            $lineTotal = round($line['qty'] * $line['unit_price'], 2);
            QuotationLine::create([
                'quotation_id' => $quotation->id,
                'description'  => $line['description'],
                'qty'          => $line['qty'],
                'unit_price'   => $line['unit_price'],
                'tax_rate'     => $line['tax_rate'] ?? 0,
                'line_total'   => $lineTotal,
                'sort_order'   => $i,
            ]);
        }
    }

    private function nextReference(): string
    {
        $prefix = config('financial.quote_prefix', 'QTE-');
        $last   = Quotation::withTrashed()
                           ->orderByDesc('created_at')
                           ->value('reference');

        if (! $last) return $prefix . '0001';
        $number = (int) str_replace($prefix, '', $last);
        return $prefix . str_pad((string) ($number + 1), 4, '0', STR_PAD_LEFT);
    }
}
