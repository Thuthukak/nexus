<?php

declare(strict_types=1);

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsModelActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => $this->activityDescription($event));
    }

    protected function activityDescription(string $event): string
    {
        $modelName = class_basename(static::class);
        $label     = $this->activityLabel ?? $modelName;

        return match ($event) {
            'created' => "{$label} created",
            'updated' => "{$label} updated",
            'deleted' => "{$label} deleted",
            'restored'=> "{$label} restored",
            default   => "{$label} {$event}",
        };
    }
}
