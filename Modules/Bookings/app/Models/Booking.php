<?php

declare(strict_types=1);

namespace Modules\Bookings\app\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasUuids, SoftDeletes;

    protected $table    = 'bk_bookings';
    protected $fillable = [
        'reference', 'service_id', 'resource_id', 'customer_user_id',
        'customer_name', 'customer_email', 'customer_phone', 'status',
        'start_at', 'end_at', 'deposit_amount', 'deposit_paid', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_at'       => 'datetime',
            'end_at'         => 'datetime',
            'deposit_amount' => 'decimal:2',
            'deposit_paid'   => 'boolean',
        ];
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_at', '>=', now())
                     ->whereIn('status', ['pending', 'confirmed']);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('start_at', today());
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'resource_id');
    }

    public function getDurationMinutesAttribute(): int
    {
        return (int) $this->start_at->diffInMinutes($this->end_at);
    }
}
