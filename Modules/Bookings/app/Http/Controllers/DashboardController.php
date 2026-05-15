<?php

declare(strict_types=1);

namespace Modules\Bookings\app\Http\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\Bookings\app\Models\Booking;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Bookings/Pages/Dashboard', [
            'stats' => [
                'today'     => Booking::today()->count(),
                'upcoming'  => Booking::upcoming()->count(),
                'pending'   => Booking::where('status', 'pending')->count(),
                'completed' => Booking::where('status', 'completed')
                                      ->whereMonth('start_at', now()->month)
                                      ->count(),
            ],
            'todaysBookings' => Booking::with('service', 'resource')
                ->today()
                ->orderBy('start_at')
                ->get()
                ->map(fn ($b) => [
                    'id'       => $b->id,
                    'customer' => $b->customer_name,
                    'service'  => $b->service?->name,
                    'resource' => $b->resource?->name,
                    'start_at' => $b->start_at?->format('H:i'),
                    'end_at'   => $b->end_at?->format('H:i'),
                    'status'   => $b->status,
                ]),
        ]);
    }
}
