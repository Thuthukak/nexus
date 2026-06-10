<?php

declare(strict_types=1);

namespace Modules\Events\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Modules\Events\app\Models\Event;
use Modules\Events\app\Models\Order;
use Modules\Events\app\Models\TicketType;
use Modules\Events\app\Services\TicketPdfService;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::withCount('orders')
            ->with('ticketTypes')
            ->orderByDesc('starts_at')
            ->get()
            ->map(fn ($e) => [
                'id'           => $e->id,
                'title'        => $e->title,
                'slug'         => $e->slug,
                'venue'        => $e->venue,
                'starts_at'    => $e->starts_at->format('d M Y H:i'),
                'status'       => $e->status,
                'is_featured'  => $e->is_featured,
                'banner_url'   => $e->banner_url,
                'total_sold'   => $e->total_sold,
                'total_capacity'=> $e->total_capacity,
                'total_revenue'=> $e->total_revenue,
                'orders_count' => $e->orders_count,
                'is_sold_out'  => $e->isSoldOut(),
            ]);

        return Inertia::render('Events/Pages/Events/Index', [
            'events' => $events,
        ]);
    }

    public function create()
    {
        return Inertia::render('Events/Pages/Events/Create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateEvent($request);

        if ($request->hasFile('banner')) {
            $validated['banner_path'] = $request->file('banner')
                ->store('events/banners', 'public');
        }

        $event = Event::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'status'     => 'draft',
        ]);

        return redirect()
            ->route('events.events.edit', $event)
            ->with('toast', ['type' => 'success', 'title' => 'Event created']);
    }

    public function edit(Event $event)
    {
        $event->load('ticketTypes');

        return Inertia::render('Events/Pages/Events/Edit', [
            'event' => $this->formatEvent($event, detailed: true),
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $validated = $this->validateEvent($request, $event);

        if ($request->hasFile('banner')) {
            if ($event->banner_path) {
                Storage::disk('public')->delete($event->banner_path);
            }
            $validated['banner_path'] = $request->file('banner')
                ->store('events/banners', 'public');
        }

        $event->update($validated);

        return back()->with('toast', ['type' => 'success', 'title' => 'Event updated']);
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()
            ->route('events.events.index')
            ->with('toast', ['type' => 'success', 'title' => 'Event deleted']);
    }

    public function orders(Event $event)
    {
        $orders = Order::where('event_id', $event->id)
            ->with('items.ticketType')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($o) => [
                'id'             => $o->id,
                'reference'      => $o->reference,
                'customer_name'  => $o->customer_name,
                'customer_email' => $o->customer_email,
                'total'          => $o->total,
                'status'         => $o->status,
                'tickets_count'  => $o->items->sum('quantity'),
                'paid_at'        => $o->paid_at?->format('d M Y H:i'),
                'created_at'     => $o->created_at->format('d M Y H:i'),
            ]);

        return Inertia::render('Events/Pages/Events/Orders', [
            'event'  => [
                'id'    => $event->id,
                'title' => $event->title,
                'slug'  => $event->slug,
            ],
            'orders'     => $orders,
            'stats'      => [
                'total_orders'  => $orders->count(),
                'paid_orders'   => $orders->where('status', 'paid')->count(),
                'total_revenue' => $orders->where('status', 'paid')->sum('total'),
                'tickets_sold'  => $orders->where('status', 'paid')->sum('tickets_count'),
            ],
        ]);
    }

    public function downloadTickets(Event $event, Order $order, TicketPdfService $pdfService)
    {
        $pdf      = $pdfService->generate($order);
        $filename = $pdfService->filename($order);
        return $pdf->download($filename);
    }

    // ── Ticket Types ──────────────────────────────────────────

    public function storeTicketType(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string',
            'price'          => 'required|numeric|min:0',
            'quantity_total' => 'required|integer|min:1',
            'max_per_order'  => 'required|integer|min:1|max:100',
            'sale_starts_at' => 'nullable|date',
            'sale_ends_at'   => 'nullable|date|after:sale_starts_at',
            'is_active'      => 'boolean',
        ]);

        $order = $event->ticketTypes()->max('sort_order') + 1;

        TicketType::create([
            ...$validated,
            'event_id'   => $event->id,
            'sort_order' => $order,
        ]);

        return back()->with('toast', ['type' => 'success', 'title' => 'Ticket type added']);
    }

    public function updateTicketType(Request $request, Event $event, TicketType $ticketType)
    {
        $ticketType->update($request->validate([
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string',
            'price'          => 'required|numeric|min:0',
            'quantity_total' => 'required|integer|min:1',
            'max_per_order'  => 'required|integer|min:1|max:100',
            'sale_starts_at' => 'nullable|date',
            'sale_ends_at'   => 'nullable|date',
            'is_active'      => 'boolean',
        ]));

        return back()->with('toast', ['type' => 'success', 'title' => 'Ticket type updated']);
    }

    public function destroyTicketType(Event $event, TicketType $ticketType)
    {
        abort_if($ticketType->quantity_sold > 0, 422, 'Cannot delete a ticket type with existing sales.');
        $ticketType->delete();

        return back()->with('toast', ['type' => 'success', 'title' => 'Ticket type deleted']);
    }

    // ── Helpers ───────────────────────────────────────────────

    private function validateEvent(Request $request, ?Event $event = null): array
    {
        return $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'venue'           => 'nullable|string|max:255',
            'venue_address'   => 'nullable|string|max:255',
            'starts_at'       => 'required|date',
            'ends_at'         => 'nullable|date|after:starts_at',
            'status'          => 'required|in:draft,published,cancelled,completed',
            'max_capacity'    => 'nullable|integer|min:1',
            'is_featured'     => 'boolean',
            'organiser_name'  => 'nullable|string|max:255',
            'organiser_email' => 'nullable|email',
            'banner'          => 'nullable|image|max:4096',
        ]);
    }

    private function formatEvent(Event $event, bool $detailed = false): array
    {
        $base = [
            'id'              => $event->id,
            'title'           => $event->title,
            'slug'            => $event->slug,
            'description'     => $event->description,
            'venue'           => $event->venue,
            'venue_address'   => $event->venue_address,
            'starts_at'       => $event->starts_at?->format('Y-m-d\TH:i'),
            'ends_at'         => $event->ends_at?->format('Y-m-d\TH:i'),
            'status'          => $event->status,
            'is_featured'     => $event->is_featured,
            'max_capacity'    => $event->max_capacity,
            'organiser_name'  => $event->organiser_name,
            'organiser_email' => $event->organiser_email,
            'banner_url'      => $event->banner_url,
            'banner_path'     => $event->banner_path,
        ];

        if (! $detailed) return $base;

        return array_merge($base, [
            'ticket_types' => $event->ticketTypes->map(fn ($t) => [
                'id'               => $t->id,
                'name'             => $t->name,
                'description'      => $t->description,
                'price'            => $t->price,
                'quantity_total'   => $t->quantity_total,
                'quantity_sold'    => $t->quantity_sold,
                'quantity_remaining'=> $t->quantity_remaining,
                'max_per_order'    => $t->max_per_order,
                'sale_starts_at'   => $t->sale_starts_at?->format('Y-m-d\TH:i'),
                'sale_ends_at'     => $t->sale_ends_at?->format('Y-m-d\TH:i'),
                'is_active'        => $t->is_active,
                'sort_order'       => $t->sort_order,
            ]),
            'total_sold'     => $event->total_sold,
            'total_capacity' => $event->total_capacity,
            'total_revenue'  => $event->total_revenue,
        ]);
    }
}
