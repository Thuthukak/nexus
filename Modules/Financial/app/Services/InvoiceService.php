<?php

declare(strict_types=1);

namespace Modules\Financial\app\Services;

use Modules\Financial\app\Models\Invoice;
use Modules\Financial\app\Models\InvoiceLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
                'notes'       => $data['notes'] ?? null,
            ]);

            foreach ($data['lines'] ?? [] as $i => $line) {
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

            $invoice->load('lines');
            $invoice->recalculate();

            return $invoice->fresh(['lines', 'customer']);
        });
    }

    public function updateStatus(Invoice $invoice, string $status): Invoice
    {
        $invoice->update(['status' => $status]);
        return $invoice->fresh();
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
}
