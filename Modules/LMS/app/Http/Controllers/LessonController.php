<?php

declare(strict_types=1);

namespace Modules\LMS\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Modules\LMS\app\Models\Course;
use Modules\LMS\app\Models\Lesson;
use Modules\LMS\app\Models\LessonFile;
use Modules\LMS\app\Models\Quiz;
use Modules\LMS\app\Models\QuizQuestion;
use Modules\LMS\app\Models\Section;

class LessonController extends Controller
{
    public function create(Course $course, Section $section)
    {
        return Inertia::render('LMS/Pages/Lessons/Create', [
            'course'  => ['id' => $course->id, 'title' => $course->title],
            'section' => ['id' => $section->id, 'title' => $section->title],
        ]);
    }

    public function store(Request $request, Course $course, Section $section)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'type'             => 'required|in:video,text,file,quiz',
            'content'          => 'nullable|string',
            'video_url'        => 'nullable|url',
            'video_type'       => 'nullable|in:embed,upload',
            'video_upload'     => 'nullable|file|max:500000|mimes:mp4,mov,avi,webm',
            'duration_minutes' => 'nullable|integer|min:0',
            'is_free_preview'  => 'boolean',
        ]);

        $order = $section->lessons()->max('order') + 1;

        // Handle video upload
        $videoPath = null;
        if ($request->hasFile('video_upload')) {
            $videoPath = $request->file('video_upload')
                ->store('private/lms/videos', 'local');
            $validated['video_type'] = 'upload';
        }

        $lesson = Lesson::create([
            'section_id'       => $section->id,
            'title'            => $validated['title'],
            'type'             => $validated['type'],
            'content'          => $validated['content'] ?? null,
            'video_url'        => $validated['video_url'] ?? null,
            'video_type'       => $validated['video_type'] ?? null,
            'video_path'       => $videoPath,
            'duration_minutes' => $validated['duration_minutes'] ?? 0,
            'order'            => $order,
            'is_free_preview'  => $validated['is_free_preview'] ?? false,
        ]);

        // Create blank quiz if type is quiz
        if ($lesson->type === 'quiz') {
            Quiz::create([
                'lesson_id'   => $lesson->id,
                'title'       => $lesson->title . ' Quiz',
                'pass_mark'   => 70,
                'max_attempts'=> 3,
                'allow_practice' => true,
                'show_answers_after' => true,
            ]);
        }

        return redirect()
            ->route('lms.courses.edit', $course)
            ->with('toast', ['type' => 'success', 'title' => 'Lesson added']);
    }

    public function edit(Course $course, Section $section, Lesson $lesson)
    {
        $lesson->load(['files', 'quiz.questions']);

        return Inertia::render('LMS/Pages/Lessons/Edit', [
            'course'  => ['id' => $course->id, 'title' => $course->title],
            'section' => ['id' => $section->id, 'title' => $section->title],
            'lesson'  => [
                'id'               => $lesson->id,
                'title'            => $lesson->title,
                'type'             => $lesson->type,
                'content'          => $lesson->content,
                'video_url'        => $lesson->video_url,
                'video_type'       => $lesson->video_type,
                'duration_minutes' => $lesson->duration_minutes,
                'is_free_preview'  => $lesson->is_free_preview,
                'files'            => $lesson->files->map(fn ($f) => [
                    'id'        => $f->id,
                    'name'      => $f->name,
                    'file_name' => $f->file_name,
                    'file_size' => $f->file_size_formatted,
                ]),
                'quiz' => $lesson->quiz ? [
                    'id'                  => $lesson->quiz->id,
                    'title'               => $lesson->quiz->title,
                    'instructions'        => $lesson->quiz->instructions,
                    'pass_mark'           => $lesson->quiz->pass_mark,
                    'max_attempts'        => $lesson->quiz->max_attempts,
                    'allow_practice'      => $lesson->quiz->allow_practice,
                    'time_limit_minutes'  => $lesson->quiz->time_limit_minutes,
                    'show_answers_after'  => $lesson->quiz->show_answers_after,
                    'randomise_questions' => $lesson->quiz->randomise_questions,
                    'questions'           => $lesson->quiz->questions->map(fn ($q) => [
                        'id'             => $q->id,
                        'question'       => $q->question,
                        'type'           => $q->type,
                        'options'        => $q->options,
                        'correct_answer' => $q->correct_answer,
                        'explanation'    => $q->explanation,
                        'marks'          => $q->marks,
                        'order'          => $q->order,
                    ]),
                ] : null,
            ],
        ]);
    }

    public function update(Request $request, Course $course, Section $section, Lesson $lesson)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'content'          => 'nullable|string',
            'video_url'        => 'nullable|url',
            'duration_minutes' => 'nullable|integer|min:0',
            'is_free_preview'  => 'boolean',
        ]);

        $lesson->update($validated);

        return back()->with('toast', ['type' => 'success', 'title' => 'Lesson updated']);
    }

    public function destroy(Course $course, Section $section, Lesson $lesson)
    {
        if ($lesson->video_path) {
            Storage::delete($lesson->video_path);
        }
        $lesson->delete();

        return redirect()
            ->route('lms.courses.edit', $course)
            ->with('toast', ['type' => 'success', 'title' => 'Lesson deleted']);
    }

    // ── File management ───────────────────────────────────────

    public function uploadFile(Request $request, Course $course, Section $section, Lesson $lesson)
    {
        $request->validate([
            'file' => 'required|file|max:51200',
            'name' => 'nullable|string|max:255',
        ]);

        $file     = $request->file('file');
        $path     = $file->store('private/lms/files', 'local');

        LessonFile::create([
            'lesson_id' => $lesson->id,
            'name'      => $request->input('name') ?: $file->getClientOriginalName(),
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('toast', ['type' => 'success', 'title' => 'File uploaded']);
    }

    public function deleteFile(Course $course, Section $section, Lesson $lesson, LessonFile $file)
    {
        Storage::delete($file->file_path);
        $file->delete();

        return back()->with('toast', ['type' => 'success', 'title' => 'File deleted']);
    }

    // ── Quiz management ───────────────────────────────────────

    public function updateQuiz(Request $request, Course $course, Section $section, Lesson $lesson)
    {
        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'instructions'        => 'nullable|string',
            'pass_mark'           => 'required|integer|min:1|max:100',
            'max_attempts'        => 'required|integer|min:1|max:10',
            'allow_practice'      => 'boolean',
            'time_limit_minutes'  => 'nullable|integer|min:1',
            'show_answers_after'  => 'boolean',
            'randomise_questions' => 'boolean',
        ]);

        $lesson->quiz->update($validated);

        return back()->with('toast', ['type' => 'success', 'title' => 'Quiz settings updated']);
    }

    public function storeQuestion(Request $request, Course $course, Section $section, Lesson $lesson)
    {
        $validated = $request->validate([
            'question'       => 'required|string',
            'type'           => 'required|in:multiple_choice,true_false',
            'options'        => 'required|array|min:2',
            'options.*'      => 'required|string',
            'correct_answer' => 'required|string',
            'explanation'    => 'nullable|string',
            'marks'          => 'required|integer|min:1',
        ]);

        $order = $lesson->quiz->questions()->max('order') + 1;

        QuizQuestion::create([
            'quiz_id'        => $lesson->quiz->id,
            'question'       => $validated['question'],
            'type'           => $validated['type'],
            'options'        => $validated['options'],
            'correct_answer' => $validated['correct_answer'],
            'explanation'    => $validated['explanation'] ?? null,
            'marks'          => $validated['marks'],
            'order'          => $order,
        ]);

        return back()->with('toast', ['type' => 'success', 'title' => 'Question added']);
    }

    public function updateQuestion(Request $request, Course $course, Section $section, Lesson $lesson, QuizQuestion $question)
    {
        $question->update($request->validate([
            'question'       => 'required|string',
            'options'        => 'required|array|min:2',
            'correct_answer' => 'required|string',
            'explanation'    => 'nullable|string',
            'marks'          => 'required|integer|min:1',
        ]));

        return back()->with('toast', ['type' => 'success', 'title' => 'Question updated']);
    }

    public function destroyQuestion(Course $course, Section $section, Lesson $lesson, QuizQuestion $question)
    {
        $question->delete();
        return back()->with('toast', ['type' => 'success', 'title' => 'Question deleted']);
    }

    public function reorderLessons(Request $request, Course $course, Section $section)
    {
        foreach ($request->input('order', []) as $i => $id) {
            Lesson::where('id', $id)->update(['order' => $i]);
        }
        return response()->json(['success' => true]);
    }
}
