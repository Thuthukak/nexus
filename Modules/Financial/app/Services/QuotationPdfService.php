<?php

declare(strict_types=1);

namespace Modules\Financial\app\Services;

use App\Facades\Settings;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Financial\app\Models\Quotation;

class QuotationPdfService
{
    private const STAMPS = [
        'draft'     => ['text' => 'DRAFT',    'color' => 'rgba(156,163,175,0.18)'],
        'sent'      => ['text' => 'PENDING',  'color' => 'rgba(37,99,235,0.13)'],
        'accepted'  => ['text' => 'ACCEPTED', 'color' => 'rgba(5,150,105,0.15)'],
        'declined'  => ['text' => 'DECLINED', 'color' => 'rgba(220,38,38,0.15)'],
        'expired'   => ['text' => 'EXPIRED',  'color' => 'rgba(156,163,175,0.18)'],
        'converted' => ['text' => 'CONVERTED','color' => 'rgba(124,58,237,0.15)'],
    ];

    public function generate(Quotation $quotation, bool $withStamp = false): \Barryvdh\DomPDF\PDF
    {
        $quotation->load(['customer', 'lines']);

        $stamp = $withStamp ? (self::STAMPS[$quotation->status] ?? null) : null;

        $pdf = Pdf::loadView('financial::pdf.quotation', [
            'quotation'     => $quotation,
            'logoUrl'       => $this->resolveLogoUrl(),
            'primaryColor'  => Settings::group('theme')->get('primary', '#1E3A5F'),
            'appName'       => Settings::group('general')->get('app_name', config('app.name')),
            'currency'      => config('financial.currency', 'ZAR'),
            'streetAddress' => Settings::group('general')->get('street_address'),
            'suburb'        => Settings::group('general')->get('suburb'),
            'city'          => Settings::group('general')->get('city'),
            'province'      => Settings::group('general')->get('province'),
            'country'       => Settings::group('general')->get('country'),
            'postalCode'    => Settings::group('general')->get('postal_code'),
            'telephone'     => Settings::group('general')->get('telephone'),
            'mobile'        => Settings::group('general')->get('mobile'),
            'website'       => Settings::group('general')->get('website'),
            'stamp'         => $stamp,
        ]);

        $pdf->setPaper('A4', 'portrait');
        return $pdf;
    }

    public function filename(Quotation $quotation): string
    {
        return 'Quotation-' . $quotation->reference . '.pdf';
    }

    private function resolveLogoUrl(): ?string
    {
        $path = Settings::group('general')->get('logo_path');
        if (! $path) return null;
        $absolutePath = storage_path('app/public/' . $path);
        return file_exists($absolutePath) ? $absolutePath : null;
    }
}
