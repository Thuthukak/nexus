<?php

declare(strict_types=1);

namespace Modules\Events\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasUuids, SoftDeletes;

    protected $table    = 'evt_orders';
    protected $fillable = [
        'reference', 'event_id', 'customer_name', 'customer_email',
        'customer_phone', 'invoice_id', 'customer_id', 'status',
        'subtotal', 'tax_total', 'total',
        'payment_token', 'payment_token_expires_at', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_token_expires_at' => 'datetime',
            'paid_at'                  => 'datetime',
            'subtotal'                 => 'decimal:2',
            'tax_total'                => 'decimal:2',
            'total'                    => 'decimal:2',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'order_id');
    }

    public function getPaymentUrlAttribute(): ?string
    {
        if (! $this->payment_token) return null;
        return url('/pay/' . $this->payment_token);
    }

    public function isPaymentTokenValid(): bool
    {
        return $this->payment_token
            && $this->payment_token_expires_at
            && $this->payment_token_expires_at->isFuture();
    }
}
