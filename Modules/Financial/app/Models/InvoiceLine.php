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
        'unit_price', 'tax_rate', 'line_total', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'qty'        => 'decimal:2',
            'unit_price' => 'decimal:2',
            'tax_rate'   => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
