<?php

declare(strict_types=1);

namespace Modules\Bookings\app\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resource extends Model
{
    use HasUuids, SoftDeletes;

    protected $table    = 'bk_resources';
    protected $fillable = [
        'name', 'type', 'capacity', 'location',
        'description', 'colour', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'capacity' => 'integer'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function availabilityRules(): HasMany
    {
        return $this->hasMany(AvailabilityRule::class, 'resource_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'resource_id');
    }
}
