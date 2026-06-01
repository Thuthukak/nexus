<?php

declare(strict_types=1);

namespace App\Listeners\HR;

use App\Notifications\LeaveApprovedNotification;
use App\Notifications\LeaveRejectedNotification;
use Modules\HR\app\Events\LeaveApplicationApproved;
use Modules\HR\app\Events\LeaveApplicationRejected;

class NotifyLeaveDecision
{
    public function handleApproved(LeaveApplicationApproved $event): void
    {
        $application = $event->application;
        $application->load('employee.user', 'leaveType');

        $employeeUser = $application->employee?->user;
        if ($employeeUser) {
            $employeeUser->notify(new LeaveApprovedNotification($application));
        }
    }

    public function handleRejected(LeaveApplicationRejected $event): void
    {
        $application = $event->application;
        $application->load('employee.user', 'leaveType');

        $employeeUser = $application->employee?->user;
        if ($employeeUser) {
            $employeeUser->notify(new LeaveRejectedNotification($application));
        }
    }
}
