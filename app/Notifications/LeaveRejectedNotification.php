<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Modules\HR\app\Models\LeaveApplication;

class LeaveRejectedNotification extends BaseNotification
{
    public function __construct(
        public readonly LeaveApplication $application
    ) {}

    public function notificationType(): string
    {
        return 'leave.rejected';
    }

    public function toDatabase(object $notifiable): array
    {
        $type   = $this->application->leaveType?->name ?? 'Leave';
        $reason = $this->application->rejection_reason
            ? " Reason: {$this->application->rejection_reason}"
            : '';

        return $this->buildDatabasePayload(
            type:   'leave.rejected',
            title:  'Leave Rejected',
            body:   "Your {$type} application has been rejected.{$reason}",
            module: 'HR',
            icon:   'x-circle',
            colour: 'red',
            action: [
                'label' => 'View',
                'url'   => "/hr/leave/{$this->application->id}",
            ],
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Leave Application Rejected')
            ->line('Your leave application has been rejected.')
            ->when($this->application->rejection_reason, fn ($m) =>
                $m->line("Reason: {$this->application->rejection_reason}")
            )
            ->action('View Details', url("/hr/leave/{$this->application->id}"));
    }
}
