<?php

declare(strict_types=1);

namespace Modules\LMS\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasUuids;

    protected $table    = 'lms_quizzes';
    protected $fillable = [
        'lesson_id', 'title', 'instructions',
        'pass_mark', 'max_attempts', 'allow_practice',
        'time_limit_minutes', 'show_answers_after', 'randomise_questions',
    ];

    protected function casts(): array
    {
        return [
            'allow_practice'      => 'boolean',
            'show_answers_after'  => 'boolean',
            'randomise_questions' => 'boolean',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id')->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id');
    }
}
