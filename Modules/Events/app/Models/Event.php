<?php

declare(strict_types=1);

namespace Modules\Events\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasUuids, SoftDeletes;

    protected $table    = 'evt_events';
    protected $fillable = [
        'title', 'slug', 'description', 'venue', 'venue_address',
        'starts_at', 'ends_at', 'banner_path', 'status',
        'max_capacity', 'is_featured', 'organiser_name',
        'organiser_email', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'   => 'datetime',
            'ends_at'     => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (! $event->slug) {
                $event->slug = Str::slug($event->title) . '-' . Str::random(5);
            }
        });
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class, 'event_id')->orderBy('sort_order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'event_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function getTotalSoldAttribute(): int
    {
        return $this->ticketTypes->sum('quantity_sold');
    }

    public function getTotalCapacityAttribute(): int
    {
        return $this->ticketTypes->sum('quantity_total');
    }

    public function getTotalRevenueAttribute(): float
    {
        return (float) $this->orders()
            ->where('status', 'paid')
            ->sum('total');
    }

    public function getAvailabilityPercentAttribute(): float
    {
        $total = $this->total_capacity;
        if ($total === 0) return 0;
        return round((($total - $this->total_sold) / $total) * 100, 1);
    }

    public function isSoldOut(): bool
    {
        return $this->ticketTypes->every(fn ($t) => $t->isAvailable() === false);
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->banner_path
            ? asset('storage/' . $this->banner_path)
            : null;
    }
}
