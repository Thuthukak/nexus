<?php

declare(strict_types=1);

namespace Modules\LMS\app\Http\Controllers\Student;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\LMS\app\Models\Enrollment;

class StudentDashboardController extends Controller
{
    public function index(Request $request)
    {
        $enrollments = Enrollment::with(['cohort.course'])
            ->where('student_id', $request->user()->id)
            ->whereIn('status', ['active', 'completed'])
            ->get()
            ->map(fn ($e) => [
                'enrollment_id'   => $e->id,
                'course_title'    => $e->cohort->course->title,
                'course_id'       => $e->cohort->course->id,
                'cohort_name'     => $e->cohort->name,
                'status'          => $e->status,
                'progress'        => $e->progress_percent,
                'completed_at'    => $e->completed_at?->format('d M Y'),
                'has_certificate' => $e->certificate !== null,
                'thumbnail_url'   => $e->cohort->course->thumbnail_path
                    ? asset('storage/' . $e->cohort->course->thumbnail_path)
                    : null,
            ]);

        return Inertia::render('Student/Dashboard', [
            'enrollments' => $enrollments,
        ]);
    }
}
