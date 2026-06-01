<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Modules\Bookings\app\Models\Booking;

class BookingCancelledNotification extends BaseNotification
{
    public function __construct(
        public readonly Booking $booking
    ) {}

    public function notificationType(): string
    {
        return 'booking.cancelled';
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->buildDatabasePayload(
            type:   'booking.cancelled',
            title:  'Booking Cancelled',
            body:   "Booking {$this->booking->reference} for {$this->booking->customer_name} has been cancelled.",
            module: 'Bookings',
            icon:   'x-circle',
            colour: 'red',
            action: [
                'label' => 'View Booking',
                'url'   => "/bookings/bookings/{$this->booking->id}",
            ],
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Booking {$this->booking->reference} Cancelled")
            ->line("Booking {$this->booking->reference} for {$this->booking->customer_name} has been cancelled.")
            ->action('View Booking', url("/bookings/bookings/{$this->booking->id}"));
    }
}
