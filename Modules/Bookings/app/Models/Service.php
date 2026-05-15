<?php

declare(strict_types=1);

namespace Modules\Bookings\app\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasUuids, SoftDeletes;

    protected $table    = 'bk_services';
    protected $fillable = [
        'name', 'description', 'duration_minutes', 'buffer_minutes',
        'price', 'max_participants', 'requires_confirmation', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'             => 'boolean',
            'requires_confirmation' => 'boolean',
            'price'                 => 'decimal:2',
            'duration_minutes'      => 'integer',
            'buffer_minutes'        => 'integer',
            'max_participants'      => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'service_id');
    }
}
