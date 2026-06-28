<?php

declare(strict_types=1);

namespace Modules\Financial\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationLine extends Model
{
    use HasUuids;

    protected $table    = 'fin_quotation_lines';
    protected $fillable = [
        'quotation_id', 'description', 'qty',
        'unit_price', 'tax_rate', 'is_tax_inclusive', 'line_total', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'qty'        => 'decimal:2',
            'unit_price' => 'decimal:2',
            'tax_rate'   => 'decimal:2',
            'line_total'       => 'decimal:2',
            'is_tax_inclusive' => 'boolean',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }
    public function taxAmount(): float
    {
        if ($this->tax_rate == 0) return 0;

        $total = (float) $this->line_total;

        if ($this->is_tax_inclusive) {
            return round($total - ($total / (1 + (float) $this->tax_rate / 100)), 2);
        }

        return round($total * ((float) $this->tax_rate / 100), 2);
    }

    public function netAmount(): float
    {
        if ($this->is_tax_inclusive) {
            return round((float) $this->line_total - $this->taxAmount(), 2);
        }
        return (float) $this->line_total;
    }
}
