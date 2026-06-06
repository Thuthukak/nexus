<?php

declare(strict_types=1);

namespace Modules\HR\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payslip extends Model
{
    use HasUuids, SoftDeletes;

    protected $table    = 'hr_payslips';
    protected $fillable = [
        'employee_id', 'uploaded_by',
        'period_year', 'period_month',
        'file_path', 'file_name',
        'gross_amount', 'net_amount', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_year'  => 'integer',
            'period_month' => 'integer',
            'gross_amount' => 'decimal:2',
            'net_amount'   => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    public function getPeriodLabelAttribute(): string
    {
        return \Carbon\Carbon::createFromDate(
            $this->period_year,
            $this->period_month,
            1
        )->format('F Y');
    }
}
