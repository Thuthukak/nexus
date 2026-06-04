<?php

declare(strict_types=1);

namespace Modules\Financial\app\Models;

use App\Traits\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasUuids, SoftDeletes, LogsModelActivity;

    protected $activityLabel = 'Customer';

    protected $table    = 'fin_customers';
    protected $fillable = [
        'user_id', 'company_name', 'contact_name',
        'email', 'phone', 'vat_number', 'address', 'is_active',
        'portal_enabled', 'portal_invited_at',
    ];

    protected function casts(): array
    {
        return [
            'portal_enabled'    => 'boolean',
            'portal_invited_at' => 'datetime',
            'is_active'         => 'boolean',
            'address'           => 'array',
        ];
    }

    public function portalUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
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
