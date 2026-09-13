<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Financial\app\Models\Invoice;

class PopUploadedNotification extends Notification
{
    public function __construct(private Invoice $invoice) {}

    public function via(): array { return ['mail']; }

    public function toMail(): MailMessage
    {
        $reviewUrl = url("/financial/invoices/{$this->invoice->id}");

        return (new MailMessage)
            ->subject("Proof of Payment received — {$this->invoice->reference}")
            ->greeting('Proof of Payment Uploaded')
            ->line(
                '**' . ($this->invoice->customer->company_name ?? $this->invoice->customer->contact_name ?? 'A customer') .
                '** has uploaded proof of payment for invoice **' . $this->invoice->reference .
                '** (R ' . number_format((float) $this->invoice->total, 2) . ').'
            )
            ->when(
                $this->invoice->pop_notes,
                fn ($m) => $m->line('Customer note: ' . $this->invoice->pop_notes)
            )
            ->action('Review & Approve', $reviewUrl);
    }
}
