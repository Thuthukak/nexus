<?php

declare(strict_types=1);

namespace Modules\Financial\app\Services;

use App\Jobs\SendInvoiceJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Financial\app\Models\Invoice;
use Modules\Financial\app\Models\RecurringInvoice;

class RecurringInvoiceService
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function createSchedule(Invoice $invoice, array $data, int $userId): RecurringInvoice
    {
        $startDate = \Carbon\Carbon::parse($data['start_date']);

        return RecurringInvoice::create([
            'source_invoice_id' => $invoice->id,
            'customer_id'       => $invoice->customer_id,
            'frequency'         => $data['frequency'],
            'interval'          => $data['interval'] ?? 1,
            'start_date'        => $startDate,
            'end_date'          => isset($data['end_date']) ? \Carbon\Carbon::parse($data['end_date']) : null,
            'max_occurrences'   => $data['max_occurrences'] ?? null,
            'next_run_date'     => $startDate,
            'auto_send'         => $data['auto_send'] ?? true,
            'due_days'          => $data['due_days'] ?? 30,
            'status'            => 'active',
            'notes'             => $data['notes'] ?? null,
            'created_by'        => $userId,
        ]);
    }

    public function processAllDue(): int
    {
        $due       = RecurringInvoice::dueToday()->with(['sourceInvoice.lines', 'customer'])->get();
        $processed = 0;

        foreach ($due as $schedule) {
            try {
                $this->processSchedule($schedule);
                $processed++;
            } catch (\Throwable $e) {
                Log::error("RecurringInvoice [{$schedule->id}] failed: " . $e->getMessage());
            }
        }

        return $processed;
    }

    public function processSchedule(RecurringInvoice $schedule): Invoice
    {
        return DB::transaction(function () use ($schedule) {
            $source = $schedule->sourceInvoice;
            $source->load('lines');

            // Create the new invoice from the source template
            $invoice = $this->invoiceService->create([
                'customer_id' => $schedule->customer_id,
                'issue_date'  => today()->format('Y-m-d'),
                'due_date'    => today()->addDays($schedule->due_days)->format('Y-m-d'),
                'currency'    => $source->currency,
                'notes'       => $source->notes,
                'lines'       => $source->lines->map(fn ($l) => [
                    'description' => $l->description,
                    'qty'         => $l->qty,
                    'unit_price'  => $l->unit_price,
                    'tax_rate'    => $l->tax_rate,
                ])->toArray(),
            ], $schedule->created_by);

            // Auto-send if configured
            if ($schedule->auto_send && $schedule->customer->email) {
                SendInvoiceJob::dispatch($invoice->id);
                $invoice->update(['status' => 'sent']);
            }

            // Update schedule
            $nextRun           = $schedule->computeNextRunDate();
            $newOccurrences    = $schedule->occurrences_count + 1;

            $schedule->update([
                'last_run_date'     => today(),
                'next_run_date'     => $nextRun,
                'occurrences_count' => $newOccurrences,
            ]);

            // Mark completed if expired after this run
            if ($schedule->isExpired()) {
                $schedule->update(['status' => 'completed']);
            }

            Log::info("RecurringInvoice [{$schedule->id}] generated {$invoice->reference}");

            return $invoice;
        });
    }

    public function pause(RecurringInvoice $schedule): void
    {
        $schedule->update(['status' => 'paused']);
    }

    public function resume(RecurringInvoice $schedule): void
    {
        $schedule->update(['status' => 'active']);
    }

    public function cancel(RecurringInvoice $schedule): void
    {
        $schedule->update(['status' => 'cancelled']);
    }
}
