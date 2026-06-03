<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Facades\Activity;

class ActivityLogService
{
    /**
     * Log a status transition on any model.
     */
    public function logStatusChange(
        Model   $subject,
        string  $from,
        string  $to,
        string  $description,
        ?string $logName = null,
    ): void {
        activity($logName ?? strtolower(class_basename($subject)))
            ->performedOn($subject)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => ['status' => $from],
                'new' => ['status' => $to],
            ])
            ->log($description);
    }

    /**
     * Log any custom event on a model.
     */
    public function log(
        Model   $subject,
        string  $description,
        array   $properties = [],
        ?string $logName    = null,
    ): void {
        activity($logName ?? strtolower(class_basename($subject)))
            ->performedOn($subject)
            ->causedBy(auth()->user())
            ->withProperties($properties)
            ->log($description);
    }

    /**
     * Log without a subject (global events like settings changes).
     */
    public function logGlobal(
        string $description,
        array  $properties = [],
        string $logName    = 'system',
    ): void {
        activity($logName)
            ->causedBy(auth()->user())
            ->withProperties($properties)
            ->log($description);
    }
}
