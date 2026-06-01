<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Modules\HR\app\Models\LeaveApplication;

class LeaveApprovedNotification extends BaseNotification
{
    public function __construct(
        public readonly LeaveApplication $application
    ) {}

    public function notificationType(): string
    {
        return 'leave.approved';
    }

    public function toDatabase(object $notifiable): array
    {
        $type = $this->application->leaveType?->name ?? 'Leave';
        return $this->buildDatabasePayload(
            type:   'leave.approved',
            title:  'Leave Approved',
            body:   "Your {$type} application has been approved.",
            module: 'HR',
            icon:   'check-circle',
            colour: 'green',
            action: [
                'label' => 'View',
                'url'   => "/hr/leave/{$this->application->id}",
            ],
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->application->leaveType?->name ?? 'Leave';
        return (new MailMessage)
            ->subject('Leave Application Approved')
            ->line("Your {$type} application has been approved.")
            ->action('View Details', url("/hr/leave/{$this->application->id}"));
    }
}
