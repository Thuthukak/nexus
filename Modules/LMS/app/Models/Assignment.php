<?php

declare(strict_types=1);

namespace Modules\LMS\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasUuids, SoftDeletes;

    protected $table    = 'lms_assignments';
    protected $fillable = [
        'course_id', 'section_id', 'title', 'description',
        'due_date', 'max_marks', 'is_required',
    ];

    protected function casts(): array
    {
        return [
            'due_date'    => 'date',
            'is_required' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class, 'assignment_id');
    }
}
