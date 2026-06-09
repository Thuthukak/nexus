<?php

declare(strict_types=1);

namespace Modules\LMS\app\Services;

use Modules\LMS\app\Models\Enrollment;
use Modules\LMS\app\Models\Quiz;
use Modules\LMS\app\Models\QuizAttempt;

class QuizService
{
    public function canAttempt(Quiz $quiz, Enrollment $enrollment, bool $isPractice): array
    {
        if ($isPractice) {
            if (! $quiz->allow_practice) {
                return ['allowed' => false, 'reason' => 'Practice mode is not enabled for this quiz.'];
            }
            return ['allowed' => true];
        }

        $realAttempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('enrollment_id', $enrollment->id)
            ->where('is_practice', false)
            ->count();

        if ($realAttempts >= $quiz->max_attempts) {
            return [
                'allowed' => false,
                'reason'  => "You have used all {$quiz->max_attempts} attempt(s).",
            ];
        }

        // Already passed?
        $alreadyPassed = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('enrollment_id', $enrollment->id)
            ->where('is_practice', false)
            ->where('passed', true)
            ->exists();

        if ($alreadyPassed) {
            return ['allowed' => false, 'reason' => 'You have already passed this quiz.'];
        }

        return ['allowed' => true, 'attempts_left' => $quiz->max_attempts - $realAttempts];
    }

    public function grade(
        Quiz       $quiz,
        Enrollment $enrollment,
        array      $answers,
        bool       $isPractice,
    ): QuizAttempt {
        $questions   = $quiz->questions;
        $marksEarned = 0;
        $marksTotal  = $questions->sum('marks');

        foreach ($questions as $question) {
            $given = $answers[$question->id] ?? null;
            if ($given !== null && $question->isCorrect((string) $given)) {
                $marksEarned += $question->marks;
            }
        }

        $score  = $marksTotal > 0 ? round(($marksEarned / $marksTotal) * 100) : 0;
        $passed = $score >= $quiz->pass_mark;

        return QuizAttempt::create([
            'enrollment_id' => $enrollment->id,
            'quiz_id'       => $quiz->id,
            'answers'       => $answers,
            'score'         => $score,
            'marks_earned'  => $marksEarned,
            'marks_total'   => $marksTotal,
            'passed'        => $passed,
            'is_practice'   => $isPractice,
            'started_at'    => now()->subMinutes(2),
            'completed_at'  => now(),
        ]);
    }

    public function getResults(QuizAttempt $attempt): array
    {
        $quiz      = $attempt->quiz;
        $questions = $quiz->questions;
        $results   = [];

        foreach ($questions as $q) {
            $given     = $attempt->answers[$q->id] ?? null;
            $isCorrect = $given !== null && $q->isCorrect((string) $given);

            $results[] = [
                'question'       => $q->question,
                'type'           => $q->type,
                'options'        => $q->options,
                'given_answer'   => $given,
                'correct_answer' => $quiz->show_answers_after ? $q->correct_answer : null,
                'explanation'    => $quiz->show_answers_after ? $q->explanation : null,
                'is_correct'     => $isCorrect,
                'marks'          => $q->marks,
                'marks_earned'   => $isCorrect ? $q->marks : 0,
            ];
        }

        return $results;
    }
}
