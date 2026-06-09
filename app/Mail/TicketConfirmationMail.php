<?php

declare(strict_types=1);

namespace App\Mail;

use App\Facades\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Events\app\Models\Order;

class TicketConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order  $order,
        public readonly string $pdfPath,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your tickets for ' . $this->order->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'events::emails.confirmation',
            with: [
                'order'   => $this->order,
                'appName' => Settings::group('general')
                    ->get('app_name', config('app.name')),
                'currency'=> config('financial.currency', 'ZAR'),
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as('Tickets-' . $this->order->reference . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
