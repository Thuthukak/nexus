<?php

declare(strict_types=1);

namespace Modules\LMS\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enrollment extends Model
{
    use HasUuids;

    protected $table    = 'lms_enrollments';
    protected $fillable = [
        'cohort_id', 'student_id', 'enrolled_by',
        'status', 'enrolled_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at'  => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class, 'cohort_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'student_id');
    }

    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'enrolled_by');
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class, 'enrollment_id');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'enrollment_id');
    }

    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class, 'enrollment_id');
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class, 'enrollment_id');
    }

    public function getProgressPercentAttribute(): float
    {
        $course  = $this->cohort->course;
        $course->load('sections.lessons');
        $total   = $course->sections->flatMap->lessons->count();
        if ($total === 0) return 0;
        $done    = $this->lessonProgress()->whereNotNull('completed_at')->count();
        return round(($done / $total) * 100, 1);
    }
}
