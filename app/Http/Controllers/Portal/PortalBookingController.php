<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Financial\app\Models\Customer;

class PortalBookingController extends Controller
{
    public function index()
    {
        $customer = $this->customer();
        if (! $customer || ! $customer->email) {
            return inertia('Portal/Bookings/Index', ['bookings' => []]);
        }

        $bookings = [];

        if (class_exists(\Modules\Bookings\app\Models\Booking::class)) {
            $bookings = \Modules\Bookings\app\Models\Booking
                ::where('customer_email', $customer->email)
                ->with(['service', 'resource'])
                ->orderByDesc('start_at')
                ->get()
                ->map(fn ($b) => [
                    'id'           => $b->id,
                    'reference'    => $b->reference,
                    'service'      => $b->service?->name,
                    'resource'     => $b->resource?->name,
                    'start_at'     => $b->start_at?->format('d M Y H:i'),
                    'end_at'       => $b->end_at?->format('d M Y H:i'),
                    'status'       => $b->status,
                    'notes'        => $b->notes,
                    'is_upcoming'  => $b->start_at?->isFuture(),
                ]);
        }

        return inertia('Portal/Bookings/Index', [
            'bookings' => $bookings,
        ]);
    }

    private function customer(): ?Customer
    {
        $user = Auth::guard('customer')->user();
        return Customer::where('user_id', $user->id)->first();
    }
}
