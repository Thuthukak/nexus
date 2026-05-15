<?php

declare(strict_types=1);

namespace Modules\Bookings\app\Services;

use Carbon\Carbon;
use Modules\Bookings\app\Models\Booking;
use Modules\Bookings\app\Models\Service;

class BookingService
{
    public function create(array $data): Booking
    {
        $service  = Service::findOrFail($data['service_id']);
        $startAt  = Carbon::parse($data['start_at']);
        $endAt    = $startAt->copy()->addMinutes(
            $service->duration_minutes + $service->buffer_minutes
        );

        return Booking::create([
            'reference'      => $this->nextReference(),
            'service_id'     => $data['service_id'],
            'resource_id'    => $data['resource_id'] ?? null,
            'customer_name'  => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'status'         => $service->requires_confirmation ? 'pending' : 'confirmed',
            'start_at'       => $startAt,
            'end_at'         => $endAt,
            'notes'          => $data['notes'] ?? null,
        ]);
    }

    private function nextReference(): string
    {
        $prefix = 'BK-';
        $last   = Booking::withTrashed()->orderByDesc('created_at')->value('reference');
        if (! $last) return $prefix . '0001';
        $number = (int) str_replace($prefix, '', $last);
        return $prefix . str_pad((string) ($number + 1), 4, '0', STR_PAD_LEFT);
    }
}
