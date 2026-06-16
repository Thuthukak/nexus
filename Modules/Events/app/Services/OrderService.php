<?php

declare(strict_types=1);

namespace Modules\Events\app\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Events\app\Models\Event;
use Modules\Events\app\Models\Order;
use Modules\Events\app\Models\OrderItem;
use Modules\Events\app\Models\Ticket;
use Modules\Events\app\Models\TicketType;
use Modules\Financial\app\Models\Customer;
use Modules\Financial\app\Services\InvoiceService;

class OrderService
{
    public function __construct(
        private InvoiceService $invoiceService,
    ) {}

    /**
     * Create an order, invoice, and redirect to payment.
     *
     * $items = [['ticket_type_id' => uuid, 'quantity' => int], ...]
     */
    public function createOrder(
        Event  $event,
        array  $customerData,
        array  $items,
    ): Order {
        return DB::transaction(function () use ($event, $customerData, $items) {

            // Validate availability and calculate totals
            $lineItems = [];
            $subtotal  = 0;

            foreach ($items as $item) {
                if (empty($item['quantity']) || (int) $item['quantity'] <= 0) continue;

                $ticketType = TicketType::lockForUpdate()->findOrFail($item['ticket_type_id']);

                abort_if(
                    ! $ticketType->isAvailable(),
                    422, "{$ticketType->name} tickets are no longer available."
                );

                abort_if(
                    $ticketType->quantity_remaining < $item['quantity'],
                    422, "Only {$ticketType->quantity_remaining} {$ticketType->name} ticket(s) remaining."
                );

                abort_if(
                    $item['quantity'] > $ticketType->max_per_order,
                    422, "Maximum {$ticketType->max_per_order} {$ticketType->name} tickets per order."
                );

                $lineSubtotal = $ticketType->price * $item['quantity'];
                $subtotal    += $lineSubtotal;

                $lineItems[] = [
                    'ticket_type' => $ticketType,
                    'quantity'    => (int) $item['quantity'],
                    'unit_price'  => $ticketType->price,
                    'subtotal'    => $lineSubtotal,
                ];
            }

            abort_if(empty($lineItems), 422, 'Please select at least one ticket.');

            // Find or create Financial customer
            $customer = Customer::firstOrCreate(
                ['email' => $customerData['email']],
                [
                    'company_name'  => $customerData['name'],
                    'contact_name'  => $customerData['name'],
                    'email'         => $customerData['email'],
                    'phone'         => $customerData['phone'] ?? null,
                    'is_active'     => true,
                ]
            );

            // Build invoice lines
            $invoiceLines = array_map(fn ($li) => [
                'description' => "{$event->title} — {$li['ticket_type']->name}",
                'qty'         => $li['quantity'],
                'unit_price'  => $li['unit_price'],
                'tax_rate'    => 0,
            ], $lineItems);

            // Create invoice via existing InvoiceService
            $invoice = $this->invoiceService->create([
                'customer_id' => $customer->id,
                'issue_date'  => today()->format('Y-m-d'),
                'due_date'    => today()->format('Y-m-d'), // due immediately
                'currency'    => config('financial.currency', 'ZAR'),
                'notes'       => "Ticket order for {$event->title} on {$event->starts_at->format('d M Y')}",
                'lines'       => $invoiceLines,
            ], 1); // system user

            // Approve + mark as sent so payment flow works
            $invoice->update(['status' => 'approved']);
            $paymentToken = $invoice->generatePaymentToken();

            // Create order
            $order = Order::create([
                'reference'                 => $this->nextReference(),
                'event_id'                  => $event->id,
                'customer_name'             => $customerData['name'],
                'customer_email'            => $customerData['email'],
                'customer_phone'            => $customerData['phone'] ?? null,
                'invoice_id'                => $invoice->id,
                'customer_id'               => $customer->id,
                'status'                    => 'pending',
                'subtotal'                  => $subtotal,
                'tax_total'                 => 0,
                'total'                     => $subtotal,
                'payment_token'             => $invoice->payment_token,
                'payment_token_expires_at'  => $invoice->payment_token_expires_at,
            ]);

            // Create order items and update sold quantities
            foreach ($lineItems as $li) {
                $orderItem = OrderItem::create([
                    'order_id'         => $order->id,
                    'ticket_type_id'   => $li['ticket_type']->id,
                    'ticket_type_name' => $li['ticket_type']->name,
                    'quantity'         => $li['quantity'],
                    'unit_price'       => $li['unit_price'],
                    'subtotal'         => $li['subtotal'],
                ]);

                // Reserve tickets (pre-generate ticket numbers)
                for ($i = 0; $i < $li['quantity']; $i++) {
                    Ticket::create([
                        'order_item_id' => $orderItem->id,
                        'order_id'      => $order->id,
                        'ticket_number' => $this->nextTicketNumber(),
                        'qr_data'       => Str::upper(Str::random(16)),
                        'status'        => 'issued',
                        'holder_name'   => $customerData['name'],
                        'holder_email'  => $customerData['email'],
                    ]);
                }

                // Increment sold count
                $li['ticket_type']->increment('quantity_sold', $li['quantity']);
            }

            return $order->fresh(['items.ticketType', 'tickets', 'event']);
        });
    }

    /**
     * Called when PayFast ITN confirms payment.
     * Marks order as paid and dispatches confirmation email.
     */
    public function markPaid(Order $order): void
    {
        if ($order->status === 'paid') return;

        $order->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        // Dispatch confirmation email with ticket PDF
        \App\Jobs\SendTicketConfirmationJob::dispatch($order->id);
    }

    private function nextReference(): string
    {
        $last = Order::withTrashed()->orderByDesc('created_at')->value('reference');
        if (! $last) return 'ORD-0001';
        $num = (int) str_replace('ORD-', '', $last);
        return 'ORD-' . str_pad((string) ($num + 1), 4, '0', STR_PAD_LEFT);
    }

    private function nextTicketNumber(): string
    {
        $prefix = 'TKT';
        $part1  = strtoupper(Str::random(4));
        $part2  = strtoupper(Str::random(4));
        return "{$prefix}-{$part1}-{$part2}";
    }
}
