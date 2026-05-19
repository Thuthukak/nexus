<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\InvoiceDispatchedNotification;
use App\Mail\InvoiceSentToCustomer;
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

class SendInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly string $invoiceId,
    ) {}

    public function handle(InvoicePdfService $pdfService): void
    {
        $invoice = Invoice::with(['customer', 'lines', 'createdBy'])
            ->findOrFail($this->invoiceId);

        if (! $invoice->customer->email) {
            Log::warning("SendInvoiceJob: customer {$invoice->customer->company_name} has no email address.");
            return;
        }

        // Generate/refresh payment token
        if ($invoice->balance_due > 0) {
            $invoice->generatePaymentToken();
        }

        // Generate PDF and save temporarily
        $pdf      = $pdfService->generate($invoice);
        $filename = $pdfService->filename($invoice);
        $tempPath = 'temp/invoices/' . $filename;

        Storage::put($tempPath, $pdf->output());
        $absolutePath = Storage::path($tempPath);

        try {
            // Send to customer
            Mail::to($invoice->customer->email)
                ->send(new InvoiceSentToCustomer($invoice, $absolutePath));

            // Notify admin
            $adminEmail = config('mail.from.admin_address');
            if ($adminEmail) {
                Mail::to($adminEmail)
                    ->send(new InvoiceDispatchedNotification($invoice));
            }

            // Mark as sent
            $invoice->update(['status' => 'sent']);

            Log::info("Invoice {$invoice->reference} sent to {$invoice->customer->email}");

        } finally {
            // Always clean up temp file
            Storage::delete($tempPath);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("SendInvoiceJob failed for invoice #{$this->invoiceId}: " . $e->getMessage());
    }
}
