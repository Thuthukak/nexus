<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\InvoiceReceiptMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Modules\Financial\app\Models\Invoice;
use Modules\Financial\app\Services\InvoicePdfService;

class SendReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly string $invoiceId,
    ) {}

    public function handle(InvoicePdfService $pdfService): void
    {
        $invoice = Invoice::with(['customer', 'lines'])
            ->findOrFail($this->invoiceId);

        if (! $invoice->customer->email) {
            Log::warning("SendReceiptJob: customer has no email for invoice #{$this->invoiceId}");
            return;
        }

        // Generate PDF with PAID stamp
        $pdf      = $pdfService->generate($invoice, withStamp: true);
        $filename = $pdfService->filename($invoice, withStamp: true);
        $tempPath = 'temp/receipts/' . $filename;

        Storage::put($tempPath, $pdf->output());
        $absolutePath = Storage::path($tempPath);

        try {
            Mail::to($invoice->customer->email)
                ->send(new InvoiceReceiptMail($invoice, $absolutePath));

            $invoice->update(['receipt_sent_at' => now()]);

            Log::info("Receipt sent for invoice {$invoice->reference} to {$invoice->customer->email}");
        } finally {
            Storage::delete($tempPath);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("SendReceiptJob failed for invoice #{$this->invoiceId}: " . $e->getMessage());
    }
}
