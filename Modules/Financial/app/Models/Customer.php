<?php

declare(strict_types=1);

namespace Modules\Financial\app\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasUuids, SoftDeletes;

    protected $table    = 'fin_customers';
    protected $fillable = [
        'user_id', 'company_name', 'contact_name',
        'email', 'phone', 'vat_number', 'address', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'address'   => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }
}
