<?php

declare(strict_types=1);

namespace Modules\Financial\app\Services;

use App\Facades\Settings;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Financial\app\Models\Invoice;

class InvoicePdfService
{
    private const STAMPS = [
        'draft'         => ['text' => 'DRAFT',         'color' => 'rgba(156,163,175,0.18)'],
        'sent'          => ['text' => 'UNPAID',         'color' => 'rgba(220,38,38,0.13)'],
        'approved'      => ['text' => 'UNPAID',         'color' => 'rgba(220,38,38,0.13)'],
        'overdue'       => ['text' => 'OVERDUE',        'color' => 'rgba(220,38,38,0.20)'],
        'deposit_paid'  => ['text' => 'DEPOSIT PAID',   'color' => 'rgba(37,99,235,0.15)'],
        'part_paid'     => ['text' => 'PART PAID',      'color' => 'rgba(217,119,6,0.15)'],
        'paid'          => ['text' => 'PAID',            'color' => 'rgba(5,150,105,0.15)'],
        'cancelled'     => ['text' => 'CANCELLED',      'color' => 'rgba(156,163,175,0.18)'],
    ];

    public function generate(Invoice $invoice, bool $withStamp = false): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['customer', 'lines']);

        $stamp = null;
        if ($withStamp) {
            $stamp = self::STAMPS[$invoice->status] ?? null;
        }

        $pdf = Pdf::loadView('financial::pdf.invoice', [
            'invoice'      => $invoice,
            'logoUrl'      => $this->resolveLogoUrl(),
            'primaryColor' => Settings::group('theme')->get('primary', '#1E3A5F'),
            'appName'      => Settings::group('general')->get('app_name', config('app.name')),
            'currency'     => config('financial.currency', 'ZAR'),
            'stamp'        => $stamp,
        ]);

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
