<?php

declare(strict_types=1);

namespace Modules\Financial\app\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use HasUuids;

    protected $table    = 'fin_tax_rates';
    protected $fillable = ['name', 'rate', 'is_compound', 'is_default', 'is_active'];

    protected function casts(): array
    {
        return [
            'rate'        => 'decimal:2',
            'is_compound' => 'boolean',
            'is_default'  => 'boolean',
            'is_active'   => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function defaultRate(): ?self
    {
        return static::where('is_default', true)->first();
    }
}
