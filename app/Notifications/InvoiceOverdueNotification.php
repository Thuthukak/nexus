<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Modules\Financial\app\Models\Invoice;

class InvoiceOverdueNotification extends BaseNotification
{
    public function __construct(
        public readonly Invoice $invoice
    ) {}

    public function notificationType(): string
    {
        return 'invoice.overdue';
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->buildDatabasePayload(
            type:   'invoice.overdue',
            title:  'Invoice Overdue',
            body:   "Invoice {$this->invoice->reference} is overdue. Balance: R " . number_format($this->invoice->balance_due, 2),
            module: 'Financial',
            icon:   'exclamation',
            colour: 'red',
            action: [
                'label' => 'View Invoice',
                'url'   => "/financial/invoices/{$this->invoice->id}",
            ],
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Invoice {$this->invoice->reference} is Overdue")
            ->line("Invoice {$this->invoice->reference} is overdue.")
            ->line('Balance due: R ' . number_format($this->invoice->balance_due, 2))
            ->action('View Invoice', url("/financial/invoices/{$this->invoice->id}"));
    }
}
