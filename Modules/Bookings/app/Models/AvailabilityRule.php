<?php

declare(strict_types=1);

namespace Modules\Bookings\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilityRule extends Model
{
    use HasUuids;

    protected $table    = 'bk_availability_rules';
    protected $fillable = [
        'resource_id', 'day_of_week', 'open_time', 'close_time', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'resource_id');
    }
}
