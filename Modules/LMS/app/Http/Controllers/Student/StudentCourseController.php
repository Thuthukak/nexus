<?php

declare(strict_types=1);

namespace Modules\LMS\app\Http\Controllers\Student;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\LMS\app\Models\Assignment;
use Modules\LMS\app\Models\Enrollment;
use Modules\LMS\app\Models\Lesson;
use Modules\LMS\app\Services\CertificateService;
use Modules\LMS\app\Services\ProgressService;
use Modules\LMS\app\Services\QuizService;

class StudentCourseController extends Controller
{
    public function __construct(
        private ProgressService    $progressService,
        private QuizService        $quizService,
        private CertificateService $certificateService,
    ) {}

    // Course overview — list of sections/lessons with progress
    public function show(Request $request, string $enrollmentId)
    {
        $enrollment = $this->findEnrollment($request, $enrollmentId);
        $course     = $enrollment->cohort->course;
        $course->load('sections.lessons.quiz', 'assignments');

        // Build completed lesson IDs for this student
        $completedIds = $enrollment->lessonProgress()
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->toArray();

        $sections = $course->sections->map(function ($section) use ($completedIds, $course) {
            return [
                'id'      => $section->id,
                'title'   => $section->title,
                'lessons' => $section->lessons->map(fn ($l) => [
                    'id'               => $l->id,
                    'title'            => $l->title,
                    'type'             => $l->type,
                    'duration_minutes' => $l->duration_minutes,
                    'completed'        => in_array($l->id, $completedIds),
                    'is_free_preview'  => $l->is_free_preview,
                    'has_quiz'         => $l->quiz !== null,
                ]),
            ];
        });

        $assignments = $course->assignments->map(fn ($a) => [
            'id'          => $a->id,
            'title'       => $a->title,
            'due_date'    => $a->due_date?->format('d M Y'),
            'max_marks'   => $a->max_marks,
            'is_required' => $a->is_required,
            'submitted'   => $enrollment->assignmentSubmissions
                ->where('assignment_id', $a->id)->isNotEmpty(),
        ]);

        return Inertia::render('Student/Course', [
            'enrollment' => [
                'id'       => $enrollment->id,
                'status'   => $enrollment->status,
                'progress' => $enrollment->progress_percent,
            ],
            'course'      => [
                'id'                  => $course->id,
                'title'               => $course->title,
                'description'         => $course->description,
                'certificate_enabled' => $course->certificate_enabled,
                'require_sequential'  => $course->require_sequential,
            ],
            'cohort'      => ['name' => $enrollment->cohort->name],
            'sections'    => $sections,
            'assignments' => $assignments,
            'completed_lessons' => $completedIds,
            'has_certificate'   => $enrollment->certificate !== null,
        ]);
    }

    // View a single lesson
    public function lesson(Request $request, string $enrollmentId, string $lessonId)
    {
        $enrollment = $this->findEnrollment($request, $enrollmentId);
        $lesson     = Lesson::with(['section.course', 'files', 'quiz.questions'])->findOrFail($lessonId);

        // Sequential check
        if ($enrollment->cohort->course->require_sequential) {
            $allLessons = $enrollment->cohort->course
                ->load('sections.lessons')->sections->flatMap->lessons;
            $lessonIndex = $allLessons->search(fn ($l) => $l->id === $lesson->id);
            if ($lessonIndex > 0) {
                $prevLesson = $allLessons->get($lessonIndex - 1);
                $prevDone   = $enrollment->lessonProgress()
                    ->where('lesson_id', $prevLesson->id)
                    ->whereNotNull('completed_at')
                    ->exists();
                if (! $prevDone) {
                    return back()->with('toast', [
                        'type'    => 'warning',
                        'message' => 'Complete the previous lesson first.',
                    ]);
                }
            }
        }

        // Mark started
        $this->progressService->markStarted($enrollment, $lesson);

        // Check existing progress
        $progress = $enrollment->lessonProgress()
            ->where('lesson_id', $lesson->id)->first();

        // Quiz attempt info
        $quizData = null;
        if ($lesson->quiz) {
            $quiz      = $lesson->quiz;
            $canAttempt = $this->quizService->canAttempt($quiz, $enrollment, false);
            $attempts   = $enrollment->quizAttempts()
                ->where('quiz_id', $quiz->id)
                ->where('is_practice', false)
                ->orderByDesc('created_at')
                ->get();
            $bestScore  = $attempts->where('passed', true)->max('score');

            $quizData = [
                'id'              => $quiz->id,
                'title'           => $quiz->title,
                'instructions'    => $quiz->instructions,
                'pass_mark'       => $quiz->pass_mark,
                'max_attempts'    => $quiz->max_attempts,
                'allow_practice'  => $quiz->allow_practice,
                'time_limit'      => $quiz->time_limit_minutes,
                'question_count'  => $quiz->questions->count(),
                'can_attempt'     => $canAttempt,
                'attempts_taken'  => $attempts->count(),
                'best_score'      => $bestScore,
                'passed'          => (bool) $attempts->where('passed', true)->count(),
            ];
        }

        return Inertia::render('Student/Lesson', [
            'enrollment' => ['id' => $enrollment->id],
            'course'     => [
                'id'    => $enrollment->cohort->course->id,
                'title' => $enrollment->cohort->course->title,
            ],
            'lesson' => [
                'id'               => $lesson->id,
                'title'            => $lesson->title,
                'type'             => $lesson->type,
                'content'          => $lesson->content,
                'embed_url'        => $lesson->embed_url,
                'video_type'       => $lesson->video_type,
                'duration_minutes' => $lesson->duration_minutes,
                'files'            => $lesson->files->map(fn ($f) => [
                    'id'   => $f->id,
                    'name' => $f->name,
                ]),
            ],
            'progress'  => [
                'completed'   => $progress?->completed_at !== null,
                'completed_at'=> $progress?->completed_at?->format('d M Y H:i'),
            ],
            'quiz' => $quizData,
        ]);
    }

    // Mark lesson complete
    public function completeLesson(Request $request, string $enrollmentId, string $lessonId)
    {
        $enrollment = $this->findEnrollment($request, $enrollmentId);
        $lesson     = Lesson::findOrFail($lessonId);
        $timeSpent  = (int) $request->input('time_spent_seconds', 0);

        $this->progressService->markComplete($enrollment, $lesson, $timeSpent);

        return back()->with('toast', ['type' => 'success', 'message' => 'Lesson marked complete!']);
    }

    // Take a quiz
    public function showQuiz(Request $request, string $enrollmentId, string $lessonId)
    {
        $enrollment = $this->findEnrollment($request, $enrollmentId);
        $lesson     = Lesson::with('quiz.questions')->findOrFail($lessonId);
        $quiz       = $lesson->quiz;

        abort_if(! $quiz, 404);

        $isPractice = (bool) $request->input('practice', false);
        $check      = $this->quizService->canAttempt($quiz, $enrollment, $isPractice);

        if (! $check['allowed']) {
            return back()->with('toast', ['type' => 'warning', 'message' => $check['reason']]);
        }

        $questions = $quiz->randomise_questions
            ? $quiz->questions->shuffle()
            : $quiz->questions;

        return Inertia::render('Student/Quiz', [
            'enrollment'  => ['id' => $enrollment->id],
            'lesson'      => ['id' => $lesson->id, 'title' => $lesson->title],
            'quiz' => [
                'id'           => $quiz->id,
                'title'        => $quiz->title,
                'instructions' => $quiz->instructions,
                'pass_mark'    => $quiz->pass_mark,
                'time_limit'   => $quiz->time_limit_minutes,
                'is_practice'  => $isPractice,
                'questions'    => $questions->map(fn ($q) => [
                    'id'      => $q->id,
                    'question'=> $q->question,
                    'type'    => $q->type,
                    'options' => $q->type === 'true_false'
                        ? ['true' => 'True', 'false' => 'False']
                        : collect($q->options)->map(fn ($opt, $k) => ['key' => $k, 'label' => $opt]),
                    'marks'   => $q->marks,
                ]),
            ],
        ]);
    }

    // Submit quiz
    public function submitQuiz(Request $request, string $enrollmentId, string $lessonId)
    {
        $enrollment = $this->findEnrollment($request, $enrollmentId);
        $lesson     = Lesson::with('quiz.questions')->findOrFail($lessonId);
        $quiz       = $lesson->quiz;

        abort_if(! $quiz, 404);

        $isPractice = (bool) $request->input('is_practice', false);
        $answers    = $request->input('answers', []);

        $check = $this->quizService->canAttempt($quiz, $enrollment, $isPractice);
        abort_if(! $check['allowed'], 422, $check['reason']);

        $attempt = $this->quizService->grade($quiz, $enrollment, $answers, $isPractice);
        $results = $this->quizService->getResults($attempt);

        // If passed and not practice, mark lesson complete
        if ($attempt->passed && ! $isPractice) {
            $this->progressService->markComplete($enrollment, $lesson);
        }

        return Inertia::render('Student/QuizResult', [
            'enrollment' => ['id' => $enrollment->id],
            'lesson'     => ['id' => $lesson->id, 'title' => $lesson->title],
            'attempt' => [
                'id'           => $attempt->id,
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

    // Download lesson file
    public function downloadFile(Request $request, string $enrollmentId, string $fileId)
    {
        $enrollment = $this->findEnrollment($request, $enrollmentId);
        $file       = \Modules\LMS\app\Models\LessonFile::findOrFail($fileId);

        return \Illuminate\Support\Facades\Storage::download(
            $file->file_path,
            $file->file_name
        );
    }

    // Download certificate
    public function downloadCertificate(Request $request, string $enrollmentId)
    {
        $enrollment = $this->findEnrollment($request, $enrollmentId);
        $cert       = $enrollment->certificate;

        abort_if(! $cert, 404, 'Certificate not available yet.');

        return $this->certificateService->download($cert);
    }

    // ── Assignment submission ─────────────────────────────────

    public function showAssignment(Request $request, string $enrollmentId, string $assignmentId)
    {
        $enrollment = $this->findEnrollment($request, $enrollmentId);
        $assignment = Assignment::findOrFail($assignmentId);

        $submission = $enrollment->assignmentSubmissions()
            ->where('assignment_id', $assignmentId)
            ->first();

        return Inertia::render('Student/Assignment', [
            'enrollment'  => ['id' => $enrollment->id, 'course_title' => $enrollment->cohort->course->title],
            'assignment'  => [
                'id'          => $assignment->id,
                'title'       => $assignment->title,
                'description' => $assignment->description,
                'due_date'    => $assignment->due_date?->format('d M Y'),
                'max_marks'   => $assignment->max_marks,
                'is_required' => $assignment->is_required,
            ],
            'submission' => $submission ? [
                'id'           => $submission->id,
                'file_name'    => $submission->file_name,
                'notes'        => $submission->notes,
                'submitted_at' => $submission->submitted_at?->format('d M Y H:i'),
                'grade'        => $submission->grade,
                'feedback'     => $submission->feedback,
                'graded_at'    => $submission->graded_at?->format('d M Y'),
            ] : null,
        ]);
    }

    public function submitAssignment(Request $request, string $enrollmentId, string $assignmentId)
    {
        $enrollment = $this->findEnrollment($request, $enrollmentId);
        $assignment = Assignment::findOrFail($assignmentId);

        $request->validate([
            'file'  => 'nullable|file|max:20480',
            'notes' => 'nullable|string|max:2000',
        ]);

        // Check for existing submission
        $existing = $enrollment->assignmentSubmissions()
            ->where('assignment_id', $assignmentId)
            ->first();

        $filePath = $existing?->file_path;
        $fileName = $existing?->file_name;

        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $filePath = $file->store('private/lms/submissions', 'local');
            $fileName = $file->getClientOriginalName();
        }

        $data = [
            'assignment_id' => $assignment->id,
            'enrollment_id' => $enrollment->id,
            'file_path'     => $filePath,
            'file_name'     => $fileName,
            'notes'         => $request->input('notes'),
            'submitted_at'  => now(),
        ];

        if ($existing) {
            $existing->update($data);
        } else {
            \Modules\LMS\app\Models\AssignmentSubmission::create($data);
        }

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Assignment submitted successfully.',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────

    private function findEnrollment(Request $request, string $enrollmentId): Enrollment
    {
        $enrollment = Enrollment::with([
            'cohort.course.sections.lessons',
            'cohort.course.assignments',
            'lessonProgress',
            'quizAttempts',
            'assignmentSubmissions',
            'certificate',
        ])->findOrFail($enrollmentId);

        // Ensure the enrollment belongs to the logged-in student
        abort_if(
            $enrollment->student_id !== $request->user()->id,
            403,
            'You do not have access to this enrollment.'
        );

        return $enrollment;
    }
}
