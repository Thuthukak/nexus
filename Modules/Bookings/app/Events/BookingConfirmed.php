<?php

declare(strict_types=1);

namespace Modules\Bookings\app\Events;

use Modules\Bookings\app\Models\Booking;

class BookingConfirmed
{
    public function __construct(
        public readonly Booking $booking
    ) {}
}
