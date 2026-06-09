<?php

declare(strict_types=1);

namespace Modules\Events\app\Services;

use App\Facades\Settings;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Events\app\Models\Order;

class TicketPdfService
{
    public function generate(Order $order): \Barryvdh\DomPDF\PDF
    {
        $order->load(['event', 'items.ticketType', 'tickets.orderItem.ticketType']);

        $appName     = Settings::group('general')->get('app_name', config('app.name'));
        $primaryColor= Settings::group('theme')->get('primary', '#1E3A5F');
        $logoPath    = $this->resolveLogoPath();

        return Pdf::loadView('events::pdf.tickets', [
            'order'        => $order,
            'appName'      => $appName,
            'primaryColor' => $primaryColor,
            'logoPath'     => $logoPath,
            'currency'     => config('financial.currency', 'ZAR'),
        ])->setPaper('A4', 'portrait');
    }

    public function filename(Order $order): string
    {
        return 'Tickets-' . $order->reference . '.pdf';
    }

    private function resolveLogoPath(): ?string
    {
        $path = Settings::group('general')->get('logo_path');
        if (! $path) return null;
        $abs = storage_path('app/public/' . $path);
        return file_exists($abs) ? $abs : null;
    }
}
