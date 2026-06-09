<?php

declare(strict_types=1);

namespace Modules\LMS\app\Services;

use Modules\LMS\app\Models\Enrollment;
use Modules\LMS\app\Models\Lesson;
use Modules\LMS\app\Models\LessonProgress;

class ProgressService
{
    public function markStarted(Enrollment $enrollment, Lesson $lesson): LessonProgress
    {
        return LessonProgress::firstOrCreate(
            ['enrollment_id' => $enrollment->id, 'lesson_id' => $lesson->id],
            ['started_at' => now()]
        );
    }

    public function markComplete(
        Enrollment $enrollment,
        Lesson     $lesson,
        int        $timeSpentSeconds = 0,
    ): LessonProgress {
        $progress = LessonProgress::firstOrCreate(
            ['enrollment_id' => $enrollment->id, 'lesson_id' => $lesson->id],
            ['started_at' => now()]
        );

        $progress->update([
            'completed_at'       => $progress->completed_at ?? now(),
            'time_spent_seconds' => $progress->time_spent_seconds + $timeSpentSeconds,
        ]);

        // Check if course is complete
        $this->checkCourseCompletion($enrollment);

        return $progress->fresh();
    }

    public function checkCourseCompletion(Enrollment $enrollment): bool
    {
        if ($enrollment->completed_at) return true;

        $course = $enrollment->cohort->course;
        $course->load('sections.lessons');

        $allLessons  = $course->sections->flatMap->lessons;
        $quizLessons = $allLessons->where('type', 'quiz');
        $totalLessons = $allLessons->count();

        if ($totalLessons === 0) return false;

        // All lessons completed
        $completedCount = $enrollment->lessonProgress()
            ->whereNotNull('completed_at')
            ->count();

        if ($completedCount < $totalLessons) return false;

        // All quizzes passed (non-practice)
        foreach ($quizLessons as $lesson) {
            $quiz = $lesson->quiz;
            if (! $quiz) continue;

            $passed = $enrollment->quizAttempts()
                ->where('quiz_id', $quiz->id)
                ->where('is_practice', false)
                ->where('passed', true)
                ->exists();

            if (! $passed) return false;
        }

        // Mark complete and issue certificate
        $enrollment->update(['completed_at' => now(), 'status' => 'completed']);
        app(CertificateService::class)->issue($enrollment);

        return true;
    }
}
