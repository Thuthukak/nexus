<?php

declare(strict_types=1);

namespace Modules\Financial\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasUuids, SoftDeletes;

    protected $table    = 'fin_invoices';
    protected $fillable = [
        'reference', 'customer_id', 'created_by', 'status',
        'issue_date', 'due_date', 'currency',
        'subtotal', 'tax_total', 'total', 'paid_total', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'issue_date'      => 'date',
            'due_date'        => 'date',
            'receipt_sent_at'   => 'datetime',
            'last_sent_at'      => 'datetime',
            'deposit_paid_at'   => 'datetime',
            'deposit_required'  => 'boolean',
            'deposit_percentage'=> 'decimal:2',
            'deposit_amount'    => 'decimal:2',
            'subtotal'   => 'decimal:2',
            'tax_total'  => 'decimal:2',
            'total'      => 'decimal:2',
            'paid_total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class, 'invoice_id')
                    ->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

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

    public function getBalanceDueAttribute(): float
    {
        return (float) $this->total - (float) $this->paid_total;
    }

    public function getDepositDueAttribute(): float
    {
        if (! $this->deposit_required) return 0;
        return max(0, (float) $this->deposit_amount - (float) $this->paid_total);
    }

    public function isDepositPaid(): bool
    {
        return $this->deposit_required && $this->paid_total >= $this->deposit_amount;
    }

    public function computeDepositAmount(): float
    {
        return round((float) $this->total * ($this->deposit_percentage / 100), 2);
    }
}
