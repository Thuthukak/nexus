<?php

declare(strict_types=1);

namespace App\Listeners\Bookings;

use App\Models\User;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\BookingConfirmedNotification;
use Modules\Bookings\app\Events\BookingCancelled;
use Modules\Bookings\app\Events\BookingConfirmed;

class NotifyBookingStatusChange
{
    public function handleConfirmed(BookingConfirmed $event): void
    {
        $booking = $event->booking;

        User::role(['Admin', 'Manager', 'Staff'])
            ->each(fn ($user) =>
                $user->notify(new BookingConfirmedNotification($booking))
            );
    }

    public function handleCancelled(BookingCancelled $event): void
    {
        $booking = $event->booking;

        User::role(['Admin', 'Manager', 'Staff'])
            ->each(fn ($user) =>
                $user->notify(new BookingCancelledNotification($booking))
            );
    }
}
