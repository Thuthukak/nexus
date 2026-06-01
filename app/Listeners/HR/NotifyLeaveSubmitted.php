<?php

declare(strict_types=1);

namespace App\Listeners\HR;

use App\Models\User;
use App\Notifications\LeaveSubmittedNotification;
use Modules\HR\app\Events\LeaveApplicationSubmitted;

class NotifyLeaveSubmitted
{
    public function handle(LeaveApplicationSubmitted $event): void
    {
        $application = $event->application;
        $application->load('employee.user', 'leaveType');

        // Notify managers and HR admins
        User::role(['Admin', 'Manager'])
            ->each(fn ($user) =>
                $user->notify(new LeaveSubmittedNotification($application))
            );
    }
}
