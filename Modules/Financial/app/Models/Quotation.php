<?php

declare(strict_types=1);

namespace Modules\Financial\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasUuids, SoftDeletes;

    protected $table    = 'fin_quotations';
    protected $fillable = [
        'reference', 'customer_id', 'created_by', 'status',
        'issue_date', 'valid_until', 'net_terms', 'currency',
        'subtotal', 'tax_total', 'total', 'notes', 'terms',
        'quote_token', 'quote_token_expires_at',
        'sent_at', 'accepted_at', 'declined_at', 'converted_invoice_id',
    ];

    protected function casts(): array
    {
        return [
            'issue_date'              => 'date',
            'valid_until'             => 'date',
            'sent_at'                 => 'datetime',
            'accepted_at'             => 'datetime',
            'declined_at'             => 'datetime',
            'quote_token_expires_at'  => 'datetime',
            'subtotal'                => 'decimal:2',
            'tax_total'               => 'decimal:2',
            'total'                   => 'decimal:2',
        ];
    }

    public const NET_TERMS = [
        'valid_7'  => ['label' => 'Valid 7 days',  'days' => 7],
        'valid_14' => ['label' => 'Valid 14 days',  'days' => 14],
        'valid_30' => ['label' => 'Valid 30 days', 'days' => 30],
        'valid_60' => ['label' => 'Valid 60 days', 'days' => 60],
        'custom'   => ['label' => 'Custom Date',   'days' => null],
    ];

    // Relations
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(QuotationLine::class, 'quotation_id')
                    ->orderBy('sort_order');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'converted_invoice_id');
    }

    // Helpers
    public function recalculate(): void
    {
        $subtotal = $this->lines->sum('line_total');
        $taxTotal = $this->lines->sum(function ($line) {
            return $line->line_total * ($line->tax_rate / 100);
        });

        $this->update([
            'subtotal'  => $subtotal,
            'tax_total' => $taxTotal,
            'total'     => $subtotal + $taxTotal,
        ]);
    }

    public function isExpired(): bool
    {
        return $this->valid_until->isPast()
            && ! in_array($this->status, ['accepted', 'declined', 'converted']);
    }

    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->update([
            'quote_token'             => $token,
            'quote_token_expires_at'  => $this->valid_until->copy()->addDays(7),
        ]);
        return $token;
    }

    public function isTokenValid(): bool
    {
        return $this->quote_token
            && $this->quote_token_expires_at
            && $this->quote_token_expires_at->isFuture();
    }

    public function getQuoteUrlAttribute(): ?string
    {
        if (! $this->quote_token) return null;
        return url('/quote/' . $this->quote_token);
    }
}
