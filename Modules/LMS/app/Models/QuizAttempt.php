<?php

declare(strict_types=1);

namespace Modules\LMS\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    use HasUuids;

    protected $table    = 'lms_quiz_attempts';
    protected $fillable = [
        'enrollment_id', 'quiz_id', 'answers',
        'score', 'marks_earned', 'marks_total',
        'passed', 'is_practice', 'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'answers'      => 'array',
            'passed'       => 'boolean',
            'is_practice'  => 'boolean',
            'started_at'   => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }
}
