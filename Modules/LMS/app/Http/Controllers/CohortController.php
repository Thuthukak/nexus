<?php

declare(strict_types=1);

namespace Modules\LMS\app\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\LMS\app\Models\Cohort;
use Modules\LMS\app\Models\Course;
use Modules\LMS\app\Services\EnrollmentService;

class CohortController extends Controller
{
    public function __construct(private EnrollmentService $enrollmentService) {}

    public function index(Course $course)
    {
        $course->load(['cohorts.teacher', 'cohorts' => fn ($q) => $q->withCount('enrollments')]);

        return Inertia::render('LMS/Pages/Cohorts/Index', [
            'course'  => ['id' => $course->id, 'title' => $course->title],
            'cohorts' => $course->cohorts->map(fn ($c) => [
                'id'                => $c->id,
                'name'              => $c->name,
                'description'       => $c->description,
                'start_date'        => $c->start_date?->format('d M Y'),
                'end_date'          => $c->end_date?->format('d M Y'),
                'max_students'      => $c->max_students,
                'enrollments_count' => $c->enrollments_count,
                'status'            => $c->status,
                'teacher'           => $c->teacher?->name,
            ]),
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'start_date'   => 'required|date',
            'end_date'     => 'nullable|date|after:start_date',
            'max_students' => 'nullable|integer|min:1',
            'teacher_id'   => 'nullable|exists:users,id',
        ]);

        Cohort::create([
            ...$validated,
            'course_id'  => $course->id,
            'status'     => 'upcoming',
            'created_by' => $request->user()->id,
        ]);

        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'Cohort created',
        ]);
    }

    public function show(Course $course, Cohort $cohort)
    {
        $cohort->load(['enrollments.student', 'enrollments' => fn ($q) =>
            $q->with('student')->orderBy('enrolled_at')
        ]);

        $students = $cohort->enrollments->map(fn ($e) => [
            'enrollment_id' => $e->id,
            'student_id'    => $e->student_id,
            'student_name'  => $e->student?->name,
            'student_email' => $e->student?->email,
            'status'        => $e->status,
            'enrolled_at'   => $e->enrolled_at?->format('d M Y'),
            'completed_at'  => $e->completed_at?->format('d M Y'),
            'progress'      => $e->progress_percent,
        ]);

        // Available students (internal, active, not already enrolled)
        $enrolledIds = $cohort->enrollments->pluck('student_id');
        $available   = User::where('guard', 'internal')
            ->where('is_active', true)
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('LMS/Pages/Cohorts/Show', [
            'course'    => ['id' => $course->id, 'title' => $course->title],
            'cohort'    => [
                'id'           => $cohort->id,
                'name'         => $cohort->name,
                'start_date'   => $cohort->start_date?->format('d M Y'),
                'end_date'     => $cohort->end_date?->format('d M Y'),
                'status'       => $cohort->status,
                'max_students' => $cohort->max_students,
            ],
            'students'  => $students,
            'available' => $available,
        ]);
    }

    public function enroll(Request $request, Course $course, Cohort $cohort)
    {
        $validated = $request->validate([
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
        ]);

        $results = $this->enrollmentService->bulkEnroll(
            $cohort,
            $validated['student_ids'],
            $request->user()->id,
        );

        $message = count($results['enrolled']) . ' student(s) enrolled.';
        if (count($results['skipped'])) {
            $message .= ' ' . count($results['skipped']) . ' skipped.';
        }

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Enrollment complete',
            'message' => $message,
        ]);
    }

    public function withdraw(Request $request, Course $course, Cohort $cohort, string $enrollmentId)
    {
        $enrollment = $cohort->enrollments()->findOrFail($enrollmentId);
        $this->enrollmentService->withdraw($enrollment);

        return back()->with('toast', ['type' => 'success', 'title' => 'Student withdrawn']);
    }
}
