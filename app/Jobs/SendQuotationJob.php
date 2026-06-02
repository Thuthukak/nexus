<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\QuotationSentMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Modules\Financial\app\Models\Quotation;
use Modules\Financial\app\Services\QuotationPdfService;

class SendQuotationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly string $quotationId
    ) {}

    public function handle(QuotationPdfService $pdfService): void
    {
        $quotation = Quotation::with(['customer', 'lines'])
            ->findOrFail($this->quotationId);

        if (! $quotation->customer->email) {
            Log::warning("SendQuotationJob: no email for {$quotation->reference}");
            return;
        }

        $pdf      = $pdfService->generate($quotation, withStamp: true);
        $filename = $pdfService->filename($quotation);
        $tempPath = 'temp/quotations/' . $filename;

        Storage::put($tempPath, $pdf->output());
        $absolutePath = Storage::path($tempPath);

        try {
            Mail::to($quotation->customer->email)
                ->send(new QuotationSentMail($quotation, $absolutePath));

            Log::info("Quotation {$quotation->reference} sent to {$quotation->customer->email}");
        } finally {
            Storage::delete($tempPath);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("SendQuotationJob failed for {$this->quotationId}: " . $e->getMessage());
    }
}
