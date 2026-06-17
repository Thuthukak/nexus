<?php

declare(strict_types=1);

namespace Modules\HR\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Financial\app\Models\Customer;

class HrDocument extends Model
{
    use HasUuids, SoftDeletes;

    protected $table    = 'hr_documents';
    protected $fillable = [
        'employee_id', 'customer_id', 'uploaded_by',
        'name', 'type', 'file_path', 'file_name',
        'mime_type', 'file_size', 'visibility',
        'expiry_date', 'is_expired', 'expiry_notified', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date'      => 'date',
            'is_expired'       => 'boolean',
            'expiry_notified'  => 'boolean',
        ];
    }

    public const TYPES = [
        'contract'     => 'Contract',
        'id_document'  => 'ID Document',
        'certificate'  => 'Certificate',
        'tax_form'     => 'Tax Form',
        'medical'      => 'Medical',
        'nda'          => 'NDA',
        'sla'          => 'SLA',
        'other'        => 'Other',
    ];

    public const VISIBILITY = [
        'web' => 'Internal Only',
        'customer' => 'Customer Visible',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        if (! $this->expiry_date) return false;
        return $this->expiry_date->isFuture()
            && $this->expiry_date->diffInDays(now()) <= $days;
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (! $this->file_size) return '—';
        $units = ['B', 'KB', 'MB', 'GB'];
        $size  = $this->file_size;
        $unit  = 0;
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        return round($size, 1) . ' ' . $units[$unit];
    }
}
