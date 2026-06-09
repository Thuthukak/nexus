<?php

declare(strict_types=1);

namespace Modules\LMS\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    use HasUuids;

    protected $table    = 'lms_assignment_submissions';
    protected $fillable = [
        'assignment_id', 'enrollment_id', 'file_path', 'file_name',
        'notes', 'submitted_at', 'grade', 'feedback', 'graded_at', 'graded_by',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'graded_at'    => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'graded_by');
    }
}
