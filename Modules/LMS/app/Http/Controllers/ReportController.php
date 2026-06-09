<?php

declare(strict_types=1);

namespace Modules\LMS\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\LMS\app\Models\Course;
use Modules\LMS\app\Models\Enrollment;
use Modules\LMS\app\Models\QuizAttempt;

class ReportController extends Controller
{
    public function courseReport(Course $course)
    {
        $course->load(['sections.lessons', 'cohorts.enrollments.student']);

        $totalEnrollments = 0;
        $completed        = 0;
        $cohortStats      = [];

        foreach ($course->cohorts as $cohort) {
            $enrollments       = $cohort->enrollments;
            $totalEnrollments += $enrollments->count();
            $completed        += $enrollments->where('status', 'completed')->count();

            $cohortStats[] = [
                'cohort_name'     => $cohort->name,
                'total'           => $enrollments->count(),
                'completed'       => $enrollments->where('status', 'completed')->count(),
                'avg_progress'    => $enrollments->avg(fn ($e) => $e->progress_percent),
            ];
        }

        return Inertia::render('LMS/Pages/Reports/Course', [
            'course' => [
                'id'    => $course->id,
                'title' => $course->title,
            ],
            'stats' => [
                'total_enrollments' => $totalEnrollments,
                'completed'         => $completed,
                'completion_rate'   => $totalEnrollments > 0
                    ? round($completed / $totalEnrollments * 100, 1)
                    : 0,
            ],
            'cohort_stats' => $cohortStats,
        ]);
    }

    public function studentReport(Request $request, string $enrollmentId)
    {
        $enrollment = Enrollment::with([
            'student', 'cohort.course.sections.lessons',
            'lessonProgress', 'quizAttempts.quiz', 'certificate',
            'assignmentSubmissions.assignment',
        ])->findOrFail($enrollmentId);

        $lessons = $enrollment->cohort->course->sections->flatMap->lessons;

        $lessonData = $lessons->map(function ($lesson) use ($enrollment) {
            $progress = $enrollment->lessonProgress
                ->firstWhere('lesson_id', $lesson->id);

            $quizData = null;
            if ($lesson->quiz) {
                $bestAttempt = $enrollment->quizAttempts
                    ->where('quiz_id', $lesson->quiz->id)
                    ->where('is_practice', false)
                    ->sortByDesc('score')
                    ->first();

                $quizData = [
                    'attempts' => $enrollment->quizAttempts
                        ->where('quiz_id', $lesson->quiz->id)
                        ->where('is_practice', false)
                        ->count(),
                    'best_score' => $bestAttempt?->score,
                    'passed'     => $bestAttempt?->passed ?? false,
                ];
            }

            return [
                'title'        => $lesson->title,
                'type'         => $lesson->type,
                'completed'    => $progress?->completed_at !== null,
                'completed_at' => $progress?->completed_at?->format('d M Y'),
                'quiz'         => $quizData,
            ];
        });

        return Inertia::render('LMS/Pages/Reports/Student', [
            'enrollment' => [
                'id'            => $enrollment->id,
                'student'       => $enrollment->student->name,
                'course'        => $enrollment->cohort->course->title,
                'cohort'        => $enrollment->cohort->name,
                'status'        => $enrollment->status,
                'enrolled_at'   => $enrollment->enrolled_at?->format('d M Y'),
                'completed_at'  => $enrollment->completed_at?->format('d M Y'),
                'progress'      => $enrollment->progress_percent,
                'has_certificate' => $enrollment->certificate !== null,
            ],
            'lessons'     => $lessonData,
            'assignments' => $enrollment->assignmentSubmissions->map(fn ($s) => [
                'title'        => $s->assignment->title,
                'submitted_at' => $s->submitted_at?->format('d M Y'),
                'grade'        => $s->grade,
                'max_marks'    => $s->assignment->max_marks,
                'graded'       => $s->graded_at !== null,
            ]),
        ]);
    }
}
