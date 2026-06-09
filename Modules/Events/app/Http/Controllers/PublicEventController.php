<?php

declare(strict_types=1);

namespace Modules\Events\app\Http\Controllers;

use App\Facades\Settings;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Events\app\Models\Event;
use Modules\Events\app\Models\Order;
use Modules\Events\app\Services\OrderService;

class PublicEventController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    public function index()
    {
        $events = Event::where('status', 'published')
            ->where('starts_at', '>=', now())
            ->with('ticketTypes')
            ->orderBy('starts_at')
            ->get()
            ->map(fn ($e) => $this->formatPublicEvent($e));

        $featured = Event::where('status', 'published')
            ->where('is_featured', true)
            ->where('starts_at', '>=', now())
            ->with('ticketTypes')
            ->orderBy('starts_at')
            ->first();

        return inertia('Events/Index', [
            'events'   => $events,
            'featured' => $featured ? $this->formatPublicEvent($featured) : null,
            'app'      => $this->appProps(),
        ]);
    }

    public function show(string $slug)
    {
        $event = Event::where('slug', $slug)
            ->where('status', 'published')
            ->with('ticketTypes')
            ->firstOrFail();

        return inertia('Events/Show', [
            'event' => $this->formatPublicEvent($event, detailed: true),
            'app'   => $this->appProps(),
        ]);
    }

    public function checkout(Request $request, string $slug)
    {
        $event = Event::where('slug', $slug)
            ->where('status', 'published')
            ->with('ticketTypes')
            ->firstOrFail();

        $validated = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_email'   => 'required|email',
            'customer_phone'   => 'nullable|string|max:30',
            'items'            => 'required|array|min:1',
            'items.*.ticket_type_id' => 'required|uuid|exists:evt_ticket_types,id',
            'items.*.quantity'       => 'required|integer|min:0',
        ]);

        $order = $this->orderService->createOrder(
            $event,
            [
                'name'  => $validated['customer_name'],
                'email' => $validated['customer_email'],
                'phone' => $validated['customer_phone'] ?? null,
            ],
            $validated['items'],
        );

        // Redirect to the existing PayFast payment page
        return redirect($order->payment_url);
    }

    public function confirmation(Request $request, string $reference)
    {
        $order = Order::where('reference', $reference)
            ->with(['event', 'items.ticketType', 'tickets'])
            ->firstOrFail();

        return inertia('Events/Confirmation', [
            'order' => [
                'reference'      => $order->reference,
                'status'         => $order->status,
                'customer_name'  => $order->customer_name,
                'customer_email' => $order->customer_email,
                'total'          => $order->total,
                'paid_at'        => $order->paid_at?->format('d M Y H:i'),
                'tickets_count'  => $order->tickets->count(),
                'items'          => $order->items->map(fn ($i) => [
                    'name'     => $i->ticket_type_name,
                    'quantity' => $i->quantity,
                    'subtotal' => $i->subtotal,
                ]),
                'event' => [
                    'title'     => $order->event->title,
                    'starts_at' => $order->event->starts_at->format('l, d F Y \a\t H:i'),
                    'venue'     => $order->event->venue,
                    'slug'      => $order->event->slug,
                ],
            ],
            'app' => $this->appProps(),
        ]);
    }

    private function formatPublicEvent(Event $event, bool $detailed = false): array
    {
        $base = [
            'id'          => $event->id,
            'title'       => $event->title,
            'slug'        => $event->slug,
            'venue'       => $event->venue,
            'venue_address'=> $event->venue_address,
            'starts_at'   => $event->starts_at->format('l, d F Y'),
            'starts_time' => $event->starts_at->format('H:i'),
            'ends_at'     => $event->ends_at?->format('H:i'),
            'starts_raw'  => $event->starts_at->toISOString(),
            'banner_url'  => $event->banner_url,
            'is_featured' => $event->is_featured,
            'is_sold_out' => $event->isSoldOut(),
            'min_price'   => $event->ticketTypes->where('is_active', true)->min('price'),
            'max_price'   => $event->ticketTypes->where('is_active', true)->max('price'),
            'organiser'   => $event->organiser_name,
        ];

        if (! $detailed) return $base;

        return array_merge($base, [
            'description'  => $event->description,
            'ticket_types' => $event->ticketTypes
                ->where('is_active', true)
                ->map(fn ($t) => [
                    'id'                => $t->id,
                    'name'              => $t->name,
                    'description'       => $t->description,
                    'price'             => $t->price,
                    'quantity_remaining'=> $t->quantity_remaining,
                    'max_per_order'     => $t->max_per_order,
                    'is_available'      => $t->isAvailable(),
                    'sale_ends_at'      => $t->sale_ends_at?->format('d M Y'),
                ]),
        ]);
    }

    private function appProps(): array
    {
        return [
            'name'     => Settings::group('general')->get('app_name', config('app.name')),
            'logo_url' => Settings::group('general')->get('logo_url'),
        ];
    }
}
