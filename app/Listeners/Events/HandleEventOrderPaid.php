<?php

declare(strict_types=1);

namespace App\Listeners\Events;

use Modules\Events\app\Models\Order;
use Modules\Events\app\Services\OrderService;
use Modules\Financial\app\Events\InvoicePaid;

class HandleEventOrderPaid
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    public function handle(InvoicePaid $event): void
    {
        // Check if this invoice belongs to an event order
        $order = Order::where('invoice_id', $event->invoice->id)
            ->where('status', 'pending')
            ->first();

        if (! $order) return;

        $this->orderService->markPaid($order);
    }
}
