<?php

declare(strict_types=1);

namespace Modules\Financial\app\Services;

use App\Facades\Settings;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Financial\app\Models\Invoice;

class InvoicePdfService
{
    public function generate(Invoice $invoice, bool $withStamp = false): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['customer', 'lines']);

        $logoUrl      = $this->resolveLogoUrl();
        $primaryColor = Settings::group('theme')->get('primary', '#1E3A5F');
        $appName      = Settings::group('general')->get('app_name', config('app.name'));
        $currency     = config('financial.currency', 'ZAR');
        $showPaidStamp = $withStamp && in_array($invoice->status, ['paid', 'part_paid']);

        $pdf = Pdf::loadView('financial::pdf.invoice', compact(
            'invoice',
            'logoUrl',
            'primaryColor',
            'appName',
            'currency',
            'showPaidStamp',
        ));

        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    public function filename(Invoice $invoice, bool $withStamp = false): string
    {
        $suffix = $withStamp ? '-RECEIPT' : '';
        return 'Invoice-' . $invoice->reference . $suffix . '.pdf';
    }

    private function resolveLogoUrl(): ?string
    {
        $path = Settings::group('general')->get('logo_path');
        if (! $path) return null;

        $absolutePath = storage_path('app/public/' . $path);
        return file_exists($absolutePath) ? $absolutePath : null;
    }
}
