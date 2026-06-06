<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Modules\HR\app\Models\HrDocument;

class DocumentExpiringNotification extends BaseNotification
{
    public function __construct(
        public readonly HrDocument $document,
        public readonly bool       $isExpired = false,
    ) {}

    public function notificationType(): string
    {
        return $this->isExpired ? 'document.expired' : 'document.expiring';
    }

    public function toDatabase(object $notifiable): array
    {
        $subject = $this->document->employee?->user?->name
            ?? $this->document->customer?->company_name
            ?? 'Unknown';

        $daysLeft = $this->isExpired
            ? 0
            : (int) now()->diffInDays($this->document->expiry_date);

        $title = $this->isExpired ? 'Document Expired' : 'Document Expiring Soon';
        $body  = $this->isExpired
            ? "{$this->document->name} ({$subject}) expired on {$this->document->expiry_date->format('d M Y')}."
            : "{$this->document->name} ({$subject}) expires in {$daysLeft} day(s) on {$this->document->expiry_date->format('d M Y')}.";

        return $this->buildDatabasePayload(
            type:   $this->notificationType(),
            title:  $title,
            body:   $body,
            module: 'HR',
            icon:   'document',
            colour: $this->isExpired ? 'red' : 'orange',
            action: $this->document->employee_id ? [
                'label' => 'View Employee',
                'url'   => "/hr/employees/{$this->document->employee_id}",
            ] : null,
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->isExpired
            ? "Document Expired: {$this->document->name}"
            : "Document Expiring Soon: {$this->document->name}";

        return (new MailMessage)
            ->subject($subject)
            ->line($this->toDatabase($notifiable)['body'])
            ->when($this->document->employee_id, fn ($m) =>
                $m->action('View Employee', url("/hr/employees/{$this->document->employee_id}"))
            );
    }
}
