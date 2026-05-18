<?php

declare(strict_types=1);

namespace Modules\Financial\app\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasUuids, SoftDeletes;

    protected $table    = 'fin_products';
    protected $fillable = [
        'name', 'description', 'default_price',
        'default_tax_rate', 'unit', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_price'    => 'decimal:2',
            'default_tax_rate' => 'decimal:2',
            'is_active'        => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
