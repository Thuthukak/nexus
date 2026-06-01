<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Modules\HR\app\Models\LeaveApplication;

class LeaveSubmittedNotification extends BaseNotification
{
    public function __construct(
        public readonly LeaveApplication $application
    ) {}

    public function notificationType(): string
    {
        return 'leave.submitted';
    }

    public function toDatabase(object $notifiable): array
    {
        $employee = $this->application->employee?->user?->name ?? 'An employee';
        $type     = $this->application->leaveType?->name ?? 'leave';

        return $this->buildDatabasePayload(
            type:   'leave.submitted',
            title:  'Leave Application',
            body:   "{$employee} has submitted a {$type} application.",
            module: 'HR',
            icon:   'calendar',
            colour: 'blue',
            action: [
                'label' => 'Review',
                'url'   => "/hr/leave/{$this->application->id}",
            ],
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        $employee = $this->application->employee?->user?->name ?? 'An employee';
        return (new MailMessage)
            ->subject('New Leave Application')
            ->line("{$employee} has submitted a leave application.")
            ->action('Review Application', url("/hr/leave/{$this->application->id}"));
    }
}
