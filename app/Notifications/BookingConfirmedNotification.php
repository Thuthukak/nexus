<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Modules\Bookings\app\Models\Booking;

class BookingConfirmedNotification extends BaseNotification
{
    public function __construct(
        public readonly Booking $booking
    ) {}

    public function notificationType(): string
    {
        return 'booking.confirmed';
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->buildDatabasePayload(
            type:   'booking.confirmed',
            title:  'Booking Confirmed',
            body:   "Booking {$this->booking->reference} for {$this->booking->customer_name} has been confirmed.",
            module: 'Bookings',
            icon:   'calendar-check',
            colour: 'green',
            action: [
                'label' => 'View Booking',
                'url'   => "/bookings/bookings/{$this->booking->id}",
            ],
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Booking {$this->booking->reference} Confirmed")
            ->line("Booking {$this->booking->reference} for {$this->booking->customer_name} has been confirmed.")
            ->action('View Booking', url("/bookings/bookings/{$this->booking->id}"));
    }
}
