<?php

declare(strict_types=1);

namespace Modules\Events\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    use HasUuids;

    protected $table    = 'evt_ticket_types';
    protected $fillable = [
        'event_id', 'name', 'description', 'price',
        'quantity_total', 'quantity_sold', 'max_per_order',
        'sale_starts_at', 'sale_ends_at', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price'          => 'decimal:2',
            'is_active'      => 'boolean',
            'sale_starts_at' => 'datetime',
            'sale_ends_at'   => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'ticket_type_id');
    }

    public function getQuantityRemainingAttribute(): int
    {
        return max(0, $this->quantity_total - $this->quantity_sold);
    }

    public function isAvailable(): bool
    {
        if (! $this->is_active) return false;
        if ($this->quantity_remaining <= 0) return false;
        if ($this->sale_starts_at && $this->sale_starts_at->isFuture()) return false;
        if ($this->sale_ends_at   && $this->sale_ends_at->isPast()) return false;
        return true;
    }
}
