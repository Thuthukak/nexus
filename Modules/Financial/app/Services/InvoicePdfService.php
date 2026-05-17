<?php

declare(strict_types=1);

namespace Modules\Financial\app\Services;

use App\Facades\Settings;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Financial\app\Models\Invoice;

class InvoicePdfService
{
    public function generate(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['customer', 'lines']);

        $logoUrl      = $this->resolveLogoUrl();
        $primaryColor = Settings::group('theme')->get('primary', '#1E3A5F');
        $appName      = Settings::group('general')->get('app_name', config('app.name'));
        $currency     = config('financial.currency', 'ZAR');

        $pdf = Pdf::loadView('financial::pdf.invoice', compact(
            'invoice',
            'logoUrl',
            'primaryColor',
            'appName',
            'currency',
        ));

        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    public function filename(Invoice $invoice): string
    {
        return 'Invoice-' . $invoice->reference . '.pdf';
    }

    private function resolveLogoUrl(): ?string
    {
        $path = Settings::group('general')->get('logo_path');
        if (! $path) return null;

        // dompdf needs an absolute file path, not a URL
        $absolutePath = storage_path('app/public/' . $path);
        return file_exists($absolutePath) ? $absolutePath : null;
    }
}
