<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\LMS\app\Models\Enrollment;

class StudentDashboardController extends Controller
{
    public function index(Request $request)
    {
        $enrollments = Enrollment::with([
            'cohort.course',
            'certificate',
        ])
        ->where('student_id', $request->user()->id)
        ->where('status', '!=', 'withdrawn')
        ->latest()
        ->get()
        ->map(fn ($e) => [
            'id'              => $e->id,
            'course_title'    => $e->cohort->course->title,
            'cohort_name'     => $e->cohort->name,
            'course_id'       => $e->cohort->course->id,
            'status'          => $e->status,
            'progress'        => $e->progress_percent,
            'start_date'      => $e->cohort->start_date?->format('d M Y'),
            'end_date'        => $e->cohort->end_date?->format('d M Y'),
            'has_certificate' => $e->certificate !== null,
            'thumbnail_url'   => $e->cohort->course->thumbnail_path
                ? asset('storage/' . $e->cohort->course->thumbnail_path)
                : null,
        ]);

        return inertia('Student/Dashboard', [
            'enrollments' => $enrollments,
            'stats' => [
                'total'     => $enrollments->count(),
                'active'    => $enrollments->where('status', 'active')->count(),
                'completed' => $enrollments->where('status', 'completed')->count(),
            ],
        ]);
    }
}
