<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\LMS\app\Models\Certificate;
use Modules\LMS\app\Models\Enrollment;
use Modules\LMS\app\Models\Lesson;
use Modules\LMS\app\Models\LessonFile;
use Modules\LMS\app\Models\Quiz;
use Modules\LMS\app\Models\QuizAttempt;
use Modules\LMS\app\Services\CertificateService;
use Modules\LMS\app\Services\ProgressService;
use Modules\LMS\app\Services\QuizService;

class StudentLearningController extends Controller
{
    public function __construct(
        private ProgressService    $progressService,
        private QuizService        $quizService,
        private CertificateService $certificateService,
    ) {}

    public function course(Request $request, string $enrollmentId)
    {
        $enrollment = $this->resolveEnrollment($request, $enrollmentId);
        $enrollment->load([
            'cohort.course.sections.lessons.quiz',
            'cohort.course.assignments',
            'lessonProgress',
            'quizAttempts',
            'certificate',
        ]);

        $course    = $enrollment->cohort->course;
        $completed = $enrollment->lessonProgress->pluck('lesson_id')->toArray();

        $sections = $course->sections->map(function ($section) use ($completed, $enrollment) {
            return [
                'id'      => $section->id,
                'title'   => $section->title,
                'lessons' => $section->lessons->map(fn ($lesson) => [
                    'id'               => $lesson->id,
                    'title'            => $lesson->title,
                    'type'             => $lesson->type,
                    'duration_minutes' => $lesson->duration_minutes,
                    'is_completed'     => in_array($lesson->id, $completed),
                    'quiz_passed'      => $lesson->quiz
                        ? $enrollment->quizAttempts
                            ->where('quiz_id', $lesson->quiz->id)
                            ->where('is_practice', false)
                            ->where('passed', true)
                            ->isNotEmpty()
                        : null,
                ]),
            ];
        });

        return inertia('Student/Course', [
            'enrollment' => [
                'id'       => $enrollment->id,
                'progress' => $enrollment->progress_percent,
                'status'   => $enrollment->status,
            ],
            'course'     => [
                'id'    => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'require_sequential' => $course->require_sequential,
            ],
            'cohort'  => ['name' => $enrollment->cohort->name],
            'sections'=> $sections,
            'has_certificate' => $enrollment->certificate !== null,
        ]);
    }

    public function lesson(Request $request, string $enrollmentId, string $lessonId)
    {
        $enrollment = $this->resolveEnrollment($request, $enrollmentId);
        $lesson     = Lesson::with(['section.course', 'files', 'quiz.questions'])->findOrFail($lessonId);

        // Mark started
        $this->progressService->markStarted($enrollment, $lesson);

        // Build quiz data (without correct answers)
        $quizData = null;
        if ($lesson->quiz) {
            $quiz         = $lesson->quiz;
            $canAttempt   = $this->quizService->canAttempt($quiz, $enrollment, false);
            $practiceOk   = $this->quizService->canAttempt($quiz, $enrollment, true);
            $realAttempts = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('enrollment_id', $enrollment->id)
                ->where('is_practice', false)
                ->orderByDesc('created_at')
                ->get();

            $questions = $quiz->randomise_questions
                ? $quiz->questions->shuffle()
                : $quiz->questions;

            $quizData = [
                'id'                 => $quiz->id,
                'title'              => $quiz->title,
                'instructions'       => $quiz->instructions,
                'pass_mark'          => $quiz->pass_mark,
                'max_attempts'       => $quiz->max_attempts,
                'allow_practice'     => $quiz->allow_practice,
                'time_limit_minutes' => $quiz->time_limit_minutes,
                'can_attempt_real'   => $canAttempt['allowed'],
                'can_attempt_practice'=> $practiceOk['allowed'],
                'cant_attempt_reason'=> $canAttempt['reason'] ?? null,
                'attempts_left'      => $canAttempt['attempts_left'] ?? 0,
                'past_attempts'      => $realAttempts->map(fn ($a) => [
                    'id'           => $a->id,
                    'score'        => $a->score,
                    'passed'       => $a->passed,
                    'completed_at' => $a->completed_at?->format('d M Y H:i'),
                    'is_practice'  => $a->is_practice,
                ]),
                'questions' => $questions->map(fn ($q) => [
                    'id'      => $q->id,
                    'question'=> $q->question,
                    'type'    => $q->type,
                    'options' => $q->type === 'true_false'
                        ? ['true' => 'True', 'false' => 'False']
                        : array_values($q->options),
                    'marks'   => $q->marks,
                ]),
            ];
        }

        return inertia('Student/Lesson', [
            'enrollment' => ['id' => $enrollment->id],
            'lesson'     => [
                'id'               => $lesson->id,
                'title'            => $lesson->title,
                'type'             => $lesson->type,
                'content'          => $lesson->content,
                'video_url'        => $lesson->video_url,
                'video_type'       => $lesson->video_type,
                'embed_url'        => $lesson->embed_url,
                'duration_minutes' => $lesson->duration_minutes,
                'files'            => $lesson->files->map(fn ($f) => [
                    'id'        => $f->id,
                    'name'      => $f->name,
                    'file_size' => $f->file_size_formatted,
                ]),
            ],
            'quiz' => $quizData,
            'section_title' => $lesson->section->title,
            'course_title'  => $lesson->section->course->title,
        ]);
    }

    public function markComplete(Request $request, string $enrollmentId, string $lessonId)
    {
        $enrollment = $this->resolveEnrollment($request, $enrollmentId);
        $lesson     = Lesson::findOrFail($lessonId);

        $this->progressService->markComplete(
            $enrollment,
            $lesson,
            $request->input('time_spent_seconds', 0)
        );

        return response()->json(['success' => true]);
    }

    public function submitQuiz(Request $request, string $enrollmentId, string $lessonId)
    {
        $enrollment = $this->resolveEnrollment($request, $enrollmentId);
        $lesson     = Lesson::with('quiz.questions')->findOrFail($lessonId);
        $quiz       = $lesson->quiz;
        $isPractice = $request->boolean('is_practice');

        $canAttempt = $this->quizService->canAttempt($quiz, $enrollment, $isPractice);
        abort_if(! $canAttempt['allowed'], 422, $canAttempt['reason']);

        $attempt = $this->quizService->grade(
            $quiz,
            $enrollment,
            $request->input('answers', []),
            $isPractice,
        );

        $results = $this->quizService->getResults($attempt);

        // If passed real attempt, mark lesson complete
        if ($attempt->passed && ! $isPractice) {
            $this->progressService->markComplete($enrollment, $lesson);
        }

        return response()->json([
            'attempt' => [
                'score'        => $attempt->score,
                'marks_earned' => $attempt->marks_earned,
                'marks_total'  => $attempt->marks_total,
                'passed'       => $attempt->passed,
                'pass_mark'    => $quiz->pass_mark,
                'is_practice'  => $attempt->is_practice,
            ],
            'results' => $results,
        ]);
    }

    public function downloadFile(Request $request, string $enrollmentId, string $fileId)
    {
        $enrollment = $this->resolveEnrollment($request, $enrollmentId);
        $file       = LessonFile::findOrFail($fileId);

        // Verify lesson belongs to enrolled course
        $courseId = $enrollment->cohort->course_id;
        abort_if(
            $file->lesson->section->course_id !== $courseId,
            403
        );

        return Storage::download($file->file_path, $file->file_name);
    }

    public function downloadCertificate(Request $request, string $enrollmentId)
    {
        $enrollment = $this->resolveEnrollment($request, $enrollmentId);
        $cert       = $enrollment->certificate;
        abort_if(! $cert, 404, 'Certificate not yet issued.');

        return $this->certificateService->download($cert);
    }

    private function resolveEnrollment(Request $request, string $enrollmentId): Enrollment
    {
        $enrollment = Enrollment::with('cohort.course')->findOrFail($enrollmentId);
        abort_if($enrollment->student_id !== $request->user()->id, 403);
        abort_if($enrollment->status === 'withdrawn', 403, 'You are no longer enrolled in this course.');
        return $enrollment;
    }
}
