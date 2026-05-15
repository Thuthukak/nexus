<?php

declare(strict_types=1);

namespace Modules\Bookings\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\Bookings\app\Models\Booking;
use Modules\Bookings\app\Models\Resource;
use Modules\Bookings\app\Models\Service;
use Modules\Bookings\app\Services\BookingService;

class BookingController extends Controller
{
    public function __construct(private BookingService $service) {}

    public function index()
    {
        $bookings = Booking::with('service', 'resource')
            ->orderByDesc('start_at')
            ->get()
            ->map(fn ($b) => [
                'id'        => $b->id,
                'reference' => $b->reference,
                'customer'  => $b->customer_name,
                'service'   => $b->service?->name,
                'resource'  => $b->resource?->name,
                'start_at'  => $b->start_at?->format('d M Y H:i'),
                'status'    => $b->status,
            ]);

        return Inertia::render('Bookings/Pages/Bookings/Index', [
            'bookings' => $bookings,
        ]);
    }

    public function create()
    {
        return Inertia::render('Bookings/Pages/Bookings/Create', [
            'services'  => Service::active()->get(['id', 'name', 'duration_minutes', 'price']),
            'resources' => Resource::active()->get(['id', 'name', 'type']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id'     => 'required|uuid|exists:bk_services,id',
            'resource_id'    => 'nullable|uuid|exists:bk_resources,id',
            'customer_name'  => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'nullable|string',
            'start_at'       => 'required|date',
            'notes'          => 'nullable|string',
        ]);

        $booking = $this->service->create($validated);

        return redirect()
            ->route('bookings.bookings.show', $booking)
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Booking created',
                'message' => "{$booking->reference} has been created.",
            ]);
    }

    public function show(Booking $booking)
    {
        $booking->load('service', 'resource');

        return Inertia::render('Bookings/Pages/Bookings/Show', [
            'booking' => [
                'id'             => $booking->id,
                'reference'      => $booking->reference,
                'status'         => $booking->status,
                'customer_name'  => $booking->customer_name,
                'customer_email' => $booking->customer_email,
                'customer_phone' => $booking->customer_phone,
                'service'        => $booking->service?->name,
                'resource'       => $booking->resource?->name,
                'start_at'       => $booking->start_at?->format('d M Y H:i'),
                'end_at'         => $booking->end_at?->format('d M Y H:i'),
                'duration'       => $booking->duration_minutes,
                'deposit_amount' => $booking->deposit_amount,
                'deposit_paid'   => $booking->deposit_paid,
                'notes'          => $booking->notes,
            ],
        ]);
    }

    public function confirm(Booking $booking)
    {
        $booking->update(['status' => 'confirmed']);
        return back()->with('toast', ['type' => 'success', 'title' => 'Booking confirmed']);
    }

    public function cancel(Booking $booking)
    {
        $booking->update(['status' => 'cancelled']);
        return back()->with('toast', ['type' => 'success', 'title' => 'Booking cancelled']);
    }

    public function complete(Booking $booking)
    {
        $booking->update(['status' => 'completed']);
        return back()->with('toast', ['type' => 'success', 'title' => 'Booking completed']);
    }
}
