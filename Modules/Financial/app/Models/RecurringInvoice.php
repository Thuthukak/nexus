<?php

declare(strict_types=1);

namespace Modules\Financial\app\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurringInvoice extends Model
{
    use HasUuids, SoftDeletes;

    protected $table    = 'fin_recurring_invoices';
    protected $fillable = [
        'source_invoice_id', 'customer_id', 'frequency', 'interval',
        'start_date', 'end_date', 'max_occurrences', 'occurrences_count',
        'next_run_date', 'last_run_date', 'auto_send', 'due_days',
        'status', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date'       => 'date',
            'end_date'         => 'date',
            'next_run_date'    => 'date',
            'last_run_date'    => 'date',
            'auto_send'        => 'boolean',
            'interval'         => 'integer',
            'due_days'         => 'integer',
            'max_occurrences'  => 'integer',
            'occurrences_count'=> 'integer',
        ];
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->where('status', 'active')
                     ->where('next_run_date', '<=', today());
    }

    // Relations
    public function sourceInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'source_invoice_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // Compute next run date from current next_run_date
    public function computeNextRunDate(): Carbon
    {
        $base = $this->next_run_date->copy();

        return match($this->frequency) {
            'daily'     => $base->addDays($this->interval),
            'weekly'    => $base->addWeeks($this->interval),
            'monthly'   => $base->addMonthsNoOverflow($this->interval),
            'quarterly' => $base->addMonthsNoOverflow($this->interval * 3),
            'yearly'    => $base->addYears($this->interval),
            default     => $base->addMonthsNoOverflow(1),
        };
    }

    public function isExpired(): bool
    {
        if ($this->end_date && $this->next_run_date->gt($this->end_date)) {
            return true;
        }
        if ($this->max_occurrences && $this->occurrences_count >= $this->max_occurrences) {
            return true;
        }
        return false;
    }

    public function frequencyLabel(): string
    {
        $labels = [
            'daily'     => 'Daily',
            'weekly'    => 'Weekly',
            'monthly'   => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly'    => 'Yearly',
        ];

        $label = $labels[$this->frequency] ?? $this->frequency;

        return $this->interval > 1
            ? "Every {$this->interval} " . rtrim($label, 'ly') . 's'
            : $label;
    }
}
