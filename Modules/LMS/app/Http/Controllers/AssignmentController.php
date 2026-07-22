<?php

declare(strict_types=1);

namespace Modules\LMS\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Modules\LMS\app\Models\Assignment;
use Modules\LMS\app\Models\AssignmentSubmission;
use Modules\LMS\app\Models\Course;

class AssignmentController extends Controller
{
    // List all assignments across all courses (teacher view)
    public function index(Request $request)
    {
        $submissions = AssignmentSubmission::with([
            'assignment.course',
            'enrollment.student',
            'enrollment.cohort',
            'gradedBy',
        ])
        ->orderByRaw('graded_at IS NULL DESC')
        ->orderByDesc('submitted_at')
        ->get()
        ->map(fn ($s) => [
            'id'            => $s->id,
            'student_name'  => $s->enrollment->student->name,
            'course_title'  => $s->assignment->course->title,
            'cohort_name'   => $s->enrollment->cohort->name,
            'assignment'    => $s->assignment->title,
            'assignment_id' => $s->assignment_id,
            'enrollment_id' => $s->enrollment_id,
            'file_name'     => $s->file_name,
            'notes'         => $s->notes,
            'submitted_at'  => $s->submitted_at?->format('d M Y H:i'),
            'grade'         => $s->grade,
            'max_marks'     => $s->assignment->max_marks,
            'feedback'      => $s->feedback,
            'graded_at'     => $s->graded_at?->format('d M Y'),
            'graded_by'     => $s->gradedBy?->name,
        ]);

        return Inertia::render('LMS/Pages/Assignments/Index', [
            'submissions' => $submissions,
        ]);
    }

    // Grade a submission
    public function grade(Request $request, AssignmentSubmission $submission)
    {
        $request->validate([
            'grade'    => 'required|integer|min:0|max:' . $submission->assignment->max_marks,
            'feedback' => 'nullable|string|max:2000',
        ]);

        $submission->update([
            'grade'      => $request->grade,
            'feedback'   => $request->feedback,
            'graded_at'  => now(),
            'graded_by'  => $request->user()->id,
        ]);

        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'Grade saved',
        ]);
    }

    // Download a student's submission file
    public function downloadSubmission(AssignmentSubmission $submission)
    {
        abort_unless($submission->file_path && Storage::exists($submission->file_path), 404);
        return Storage::download($submission->file_path, $submission->file_name);
    }

    // Manage assignments for a course
    public function courseAssignments(Course $course)
    {
        $course->load(['assignments.submissions', 'sections']);

        return Inertia::render('LMS/Pages/Assignments/Course', [
            'course' => [
                'id'    => $course->id,
                'title' => $course->title,
            ],
            'sections'    => $course->sections->map(fn ($s) => ['id' => $s->id, 'title' => $s->title]),
            'assignments' => $course->assignments->map(fn ($a) => [
                'id'               => $a->id,
                'title'            => $a->title,
                'description'      => $a->description,
                'due_date'         => $a->due_date?->format('d M Y'),
                'max_marks'        => $a->max_marks,
                'is_required'      => $a->is_required,
                'submissions_count'=> $a->submissions->count(),
                'graded_count'     => $a->submissions->whereNotNull('graded_at')->count(),
            ]),
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'section_id'  => 'nullable|exists:lms_sections,id',
            'due_date'    => 'nullable|date',
            'max_marks'   => 'required|integer|min:1|max:1000',
            'is_required' => 'boolean',
        ]);

        Assignment::create([...$validated, 'course_id' => $course->id]);

        return back()->with('toast', ['type' => 'success', 'title' => 'Assignment created']);
    }

    public function update(Request $request, Course $course, Assignment $assignment)
    {
        $assignment->update($request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'due_date'    => 'nullable|date',
            'max_marks'   => 'required|integer|min:1|max:1000',
            'is_required' => 'boolean',
        ]));

        return back()->with('toast', ['type' => 'success', 'title' => 'Assignment updated']);
    }

    public function destroy(Course $course, Assignment $assignment)
    {
        $assignment->delete();
        return back()->with('toast', ['type' => 'success', 'title' => 'Assignment deleted']);
    }
}
