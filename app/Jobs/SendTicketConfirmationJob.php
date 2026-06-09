<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\TicketConfirmationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Modules\Events\app\Models\Order;
use Modules\Events\app\Services\TicketPdfService;

class SendTicketConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly string $orderId
    ) {}

    public function handle(TicketPdfService $pdfService): void
    {
        $order = Order::with([
            'event',
            'items.ticketType',
            'tickets.orderItem.ticketType',
        ])->findOrFail($this->orderId);

        $pdf      = $pdfService->generate($order);
        $filename = $pdfService->filename($order);
        $tempPath = 'temp/tickets/' . $filename;

        Storage::put($tempPath, $pdf->output());

        try {
            Mail::to($order->customer_email)
                ->send(new TicketConfirmationMail($order, Storage::path($tempPath)));

            Log::info("Ticket confirmation sent for order {$order->reference}");
        } finally {
            Storage::delete($tempPath);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("SendTicketConfirmationJob failed for {$this->orderId}: " . $e->getMessage());
    }
}
