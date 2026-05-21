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
        'payment_token', 'payment_token_expires_at', 'deposit_required', 
        'deposit_type', 'deposit_percentage', 'deposit_amount', 'deposit_paid_at',
        'receipt_sent_at', 'last_sent_at', 'last_reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'issue_date'      => 'date',
            'due_date'        => 'date',
            'receipt_sent_at'   => 'datetime',
            'last_sent_at'      => 'datetime',
            'deposit_paid_at'            => 'datetime',
            'payment_token_expires_at'   => 'datetime',
            'last_reminder_sent_at'      => 'datetime',
            'deposit_required'  => 'boolean',
            'deposit_type'      => 'string',
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
            'subtotal'   => $subtotal,
            'tax_total'  => $taxTotal,
            'total'      => $subtotal + $taxTotal,
            'paid_total' => $this->paid_total ?? 0,
        ]);
    }

    public function getBalanceDueAttribute(): float
    {
        return round((float) ($this->total ?? 0) - (float) ($this->paid_total ?? 0), 2);
    }

    public const NET_TERMS = [
        'net_7'   => ['label' => 'Net 7',   'days' => 7],
        'net_14'  => ['label' => 'Net 14',  'days' => 14],
        'net_30'  => ['label' => 'Net 30',  'days' => 30],
        'net_45'  => ['label' => 'Net 45',  'days' => 45],
        'net_60'  => ['label' => 'Net 60',  'days' => 60],
        'net_90'  => ['label' => 'Net 90',  'days' => 90],
        'due_on_receipt' => ['label' => 'Due on Receipt', 'days' => 0],
        'custom'  => ['label' => 'Custom Date', 'days' => null],
    ];

    public function generatePaymentToken(?int $graceDays = null): string
    {
        try {
            $graceDays = $graceDays ?? (int) \App\Facades\Settings::group('payments')->get('grace_days', 7);
        } catch (\Throwable $e) {
            $graceDays = 7;
            \Illuminate\Support\Facades\Log::warning('generatePaymentToken: Settings facade failed, using default grace days.', [
                'error' => $e->getMessage(),
            ]);
        }

        $expiry = $this->due_date
            ? $this->due_date->copy()->addDays($graceDays)
            : now()->addDays(30);

        $token = bin2hex(random_bytes(32));
        $this->update([
            'payment_token'            => $token,
            'payment_token_expires_at' => $expiry,
        ]);
        return $token;
    }

    public function isPaymentTokenValid(): bool
    {
        return $this->payment_token
            && $this->payment_token_expires_at
            && $this->payment_token_expires_at->isFuture();
    }

    public function getPaymentUrlAttribute(): ?string
    {
        if (! $this->payment_token) return null;
        return url('/pay/' . $this->payment_token);
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
        if ($this->deposit_type === 'fixed') {
            return (float) $this->deposit_amount;
        }
        return round((float) $this->total * ((float) $this->deposit_percentage / 100), 2);
    }

    /**
     * The amount the customer should pay RIGHT NOW.
     * Backend decides — never the frontend.
     */
    public function amountDueNow(): float
    {
        if (! $this->deposit_required) {
            return (float) $this->balance_due;
        }

        // Deposit not yet paid — charge the deposit
        if (! $this->deposit_paid_at && $this->paid_total < $this->deposit_amount) {
            return max(0, (float) $this->deposit_amount - (float) $this->paid_total);
        }

        // Deposit paid — charge the remaining balance
        return (float) $this->balance_due;
    }

    public function paymentStageLabel(): string
    {
        if (! $this->deposit_required) {
            return 'Full Payment';
        }

        if (! $this->deposit_paid_at && $this->paid_total < $this->deposit_amount) {
            return 'Deposit Payment';
        }

        return 'Balance Payment';
    }
}
