<?php

declare(strict_types=1);

namespace Modules\Financial\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    use HasUuids;

    protected $table    = 'fin_invoice_lines';
    protected $fillable = [
        'invoice_id', 'description', 'qty',
        'unit_price', 'tax_rate', 'is_tax_inclusive',
        'line_total', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'qty'              => 'decimal:2',
            'unit_price'       => 'decimal:2',
            'tax_rate'         => 'decimal:2',
            'line_total'       => 'decimal:2',
            'is_tax_inclusive' => 'boolean',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Extract the VAT portion from an inclusive line total.
     *
     * Inclusive formula:  tax = total - (total / (1 + rate/100))
     * Exclusive formula:  tax = total * (rate / 100)
     */
    public function taxAmount(): float
    {
        if ($this->tax_rate == 0) return 0;

        $total = (float) $this->line_total;

        if ($this->is_tax_inclusive) {
            // VAT is inside the price — extract it
            return round($total - ($total / (1 + (float) $this->tax_rate / 100)), 2);
        }

        // VAT is on top — add it
        return round($total * ((float) $this->tax_rate / 100), 2);
    }

    /**
     * The net (ex-VAT) amount for this line.
     */
    public function netAmount(): float
    {
        if ($this->is_tax_inclusive) {
            return round((float) $this->line_total - $this->taxAmount(), 2);
        }

        return (float) $this->line_total;
    }
}
