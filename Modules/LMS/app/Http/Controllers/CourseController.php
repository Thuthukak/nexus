<?php

declare(strict_types=1);

namespace Modules\LMS\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Modules\LMS\app\Models\Course;
use Modules\LMS\app\Models\Section;
use Modules\LMS\app\Models\Lesson;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::withCount(['sections', 'cohorts'])
            ->with('createdBy')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($c) => [
                'id'              => $c->id,
                'title'           => $c->title,
                'category'        => $c->category,
                'status'          => $c->status,
                'difficulty'      => $c->difficulty,
                'estimated_hours' => $c->estimated_hours,
                'sections_count'  => $c->sections_count,
                'cohorts_count'   => $c->cohorts_count,
                'created_by'      => $c->createdBy?->name,
                'created_at'      => $c->created_at->format('d M Y'),
            ]);

        return Inertia::render('LMS/Pages/Courses/Index', [
            'courses' => $courses,
        ]);
    }

    public function create()
    {
        return Inertia::render('LMS/Pages/Courses/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'category'            => 'nullable|string|max:100',
            'difficulty'          => 'required|in:beginner,intermediate,advanced',
            'estimated_hours'     => 'nullable|integer|min:0',
            'certificate_enabled' => 'boolean',
            'require_sequential'  => 'boolean',
            'thumbnail'           => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail_path'] = $request->file('thumbnail')
                ->store('lms/thumbnails', 'public');
        }

        $course = Course::create([
            ...$validated,
            'status'     => 'draft',
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('lms.courses.edit', $course)
            ->with('toast', ['type' => 'success', 'title' => 'Course created']);
    }

    public function show(Course $course)
    {
        $course->load(['sections.lessons.quiz', 'sections.lessons.files', 'cohorts.teacher']);

        return Inertia::render('LMS/Pages/Courses/Show', [
            'course' => $this->formatCourse($course, detailed: true),
        ]);
    }

    public function edit(Course $course)
    {
        $course->load(['sections.lessons.quiz', 'sections.lessons.files']);

        return Inertia::render('LMS/Pages/Courses/Edit', [
            'course' => $this->formatCourse($course, detailed: true),
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'category'            => 'nullable|string|max:100',
            'status'              => 'required|in:draft,published,archived',
            'difficulty'          => 'required|in:beginner,intermediate,advanced',
            'estimated_hours'     => 'nullable|integer|min:0',
            'certificate_enabled' => 'boolean',
            'require_sequential'  => 'boolean',
            'thumbnail'           => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail_path) {
                Storage::disk('public')->delete($course->thumbnail_path);
            }
            $validated['thumbnail_path'] = $request->file('thumbnail')
                ->store('lms/thumbnails', 'public');
        }

        $course->update($validated);

        return back()->with('toast', ['type' => 'success', 'title' => 'Course updated']);
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()
            ->route('lms.courses.index')
            ->with('toast', ['type' => 'success', 'title' => 'Course deleted']);
    }

    // ── Section management ────────────────────────────────────

    public function storeSection(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $order = $course->sections()->max('order') + 1;

        Section::create([
            'course_id'   => $course->id,
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'order'       => $order,
        ]);

        return back()->with('toast', ['type' => 'success', 'title' => 'Section added']);
    }

    public function updateSection(Request $request, Course $course, Section $section)
    {
        $section->update($request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]));

        return back()->with('toast', ['type' => 'success', 'title' => 'Section updated']);
    }

    public function destroySection(Course $course, Section $section)
    {
        $section->delete();
        return back()->with('toast', ['type' => 'success', 'title' => 'Section deleted']);
    }

    public function reorderSections(Request $request, Course $course)
    {
        foreach ($request->input('order', []) as $i => $id) {
            Section::where('id', $id)->update(['order' => $i]);
        }
        return response()->json(['success' => true]);
    }

    private function formatCourse(Course $course, bool $detailed = false): array
    {
        $base = [
            'id'                  => $course->id,
            'title'               => $course->title,
            'description'         => $course->description,
            'category'            => $course->category,
            'status'              => $course->status,
            'difficulty'          => $course->difficulty,
            'estimated_hours'     => $course->estimated_hours,
            'certificate_enabled' => $course->certificate_enabled,
            'require_sequential'  => $course->require_sequential,
            'thumbnail_url'       => $course->thumbnail_path
                ? asset('storage/' . $course->thumbnail_path)
                : null,
        ];

        if (! $detailed) return $base;

        return array_merge($base, [
            'sections' => $course->sections->map(fn ($s) => [
                'id'          => $s->id,
                'title'       => $s->title,
                'description' => $s->description,
                'order'       => $s->order,
                'lessons'     => $s->lessons->map(fn ($l) => [
                    'id'               => $l->id,
                    'title'            => $l->title,
                    'type'             => $l->type,
                    'order'            => $l->order,
                    'duration_minutes' => $l->duration_minutes,
                    'is_free_preview'  => $l->is_free_preview,
                    'has_quiz'         => $l->quiz !== null,
                    'files_count'      => $l->files->count(),
                    'video_type'       => $l->video_type,
                ]),
            ]),
        ]);
    }
}
