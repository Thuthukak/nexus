<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Financial\app\Models\Invoice;

class InvoiceSentToCustomer extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice,
        public readonly string  $pdfPath,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice ' . $this->invoice->reference . ' from ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'financial::emails.invoice-sent',
            with: [
                'invoice'  => $this->invoice,
                'appName'  => config('app.name'),
                'currency' => config('financial.currency', 'ZAR'),
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as('Invoice-' . $this->invoice->reference . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
