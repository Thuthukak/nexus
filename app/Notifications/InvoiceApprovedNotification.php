<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Modules\Financial\app\Models\Invoice;

class InvoiceApprovedNotification extends BaseNotification
{
    public function __construct(
        public readonly Invoice $invoice
    ) {}

    public function notificationType(): string
    {
        return 'invoice.approved';
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->buildDatabasePayload(
            type:   'invoice.approved',
            title:  'Invoice Approved',
            body:   "Invoice {$this->invoice->reference} has been approved.",
            module: 'Financial',
            icon:   'check-circle',
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
            ->subject("Invoice {$this->invoice->reference} Approved")
            ->line("Invoice {$this->invoice->reference} has been approved.")
            ->action('View Invoice', url("/financial/invoices/{$this->invoice->id}"));
    }
}
