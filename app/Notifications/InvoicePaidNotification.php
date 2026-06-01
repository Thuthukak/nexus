<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Modules\Financial\app\Models\Invoice;

class InvoicePaidNotification extends BaseNotification
{
    public function __construct(
        public readonly Invoice $invoice
    ) {}

    public function notificationType(): string
    {
        return 'invoice.paid';
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->buildDatabasePayload(
            type:   'invoice.paid',
            title:  'Invoice Paid',
            body:   "Invoice {$this->invoice->reference} has been fully paid.",
            module: 'Financial',
            icon:   'currency',
            colour: 'green',
            action: [
                'label' => 'View Invoice',
                'url'   => "/financial/invoices/{$this->invoice->id}",
            ],
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Invoice {$this->invoice->reference} Paid")
            ->line("Invoice {$this->invoice->reference} has been fully paid.")
            ->action('View Invoice', url("/financial/invoices/{$this->invoice->id}"));
    }
}
