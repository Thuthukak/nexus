<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Financial\app\Models\Invoice;

class InvoiceDispatchedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . config('app.name') . '] Invoice ' . $this->invoice->reference . ' sent to customer',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'financial::emails.invoice-dispatched-admin',
            with: [
                'invoice'  => $this->invoice,
                'appName'  => config('app.name'),
                'currency' => config('financial.currency', 'ZAR'),
            ],
        );
    }
}
