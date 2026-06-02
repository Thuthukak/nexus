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
use Modules\Financial\app\Models\Quotation;

class QuotationSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Quotation $quotation,
        public readonly string    $pdfPath,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Quotation ' . $this->quotation->reference . ' from ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'financial::emails.quotation-sent',
            with: [
                'quotation' => $this->quotation,
                'appName'   => config('app.name'),
                'currency'  => config('financial.currency', 'ZAR'),
                'quoteUrl'  => $this->quotation->quote_url,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as('Quotation-' . $this->quotation->reference . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
