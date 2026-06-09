<?php

declare(strict_types=1);

namespace Modules\LMS\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizQuestion extends Model
{
    use HasUuids;

    protected $table    = 'lms_quiz_questions';
    protected $fillable = [
        'quiz_id', 'question', 'type', 'options',
        'correct_answer', 'explanation', 'marks', 'order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'marks'   => 'integer',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function isCorrect(string $answer): bool
    {
        return $answer === $this->correct_answer;
    }

    public function getTrueFalseOptionsAttribute(): array
    {
        return ['true' => 'True', 'false' => 'False'];
    }
}
