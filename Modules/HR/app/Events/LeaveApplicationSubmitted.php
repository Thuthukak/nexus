<?php

declare(strict_types=1);

namespace Modules\HR\app\Events;

use Modules\HR\app\Models\LeaveApplication;

class LeaveApplicationSubmitted
{
    public function __construct(
        public readonly LeaveApplication $application
    ) {}
}
