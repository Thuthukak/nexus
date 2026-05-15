<?php

declare(strict_types=1);

namespace Modules\HR\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasUuids;

    protected $table    = 'hr_leave_types';
    protected $fillable = [
        'name', 'days_per_year', 'requires_approval', 'is_paid', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'requires_approval' => 'boolean',
            'is_paid'           => 'boolean',
            'is_active'         => 'boolean',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class, 'leave_type_id');
    }
}
