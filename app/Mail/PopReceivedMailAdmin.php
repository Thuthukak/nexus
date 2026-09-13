<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Modules\Financial\app\Models\Invoice;

class PopReceivedMailAdmin extends Mailable
{
    public function __construct(public Invoice $invoice) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Proof of payment for — {$this->invoice->reference}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.pop-received-admin');
    }
}
