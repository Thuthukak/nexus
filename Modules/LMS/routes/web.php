<?php

use Illuminate\Support\Facades\Route;
use Modules\LMS\app\Http\Controllers\CourseController;
use Modules\LMS\app\Http\Controllers\LessonController;
use Modules\LMS\app\Http\Controllers\CohortController;
use Modules\LMS\app\Http\Controllers\ReportController;

// Courses
Route::resource('courses', CourseController::class);

// Section management (within course edit)
Route::prefix('courses/{course}')->name('courses.')->group(function () {
    Route::post('/sections',                [CourseController::class, 'storeSection'])->name('sections.store');
    Route::patch('/sections/{section}',     [CourseController::class, 'updateSection'])->name('sections.update');
    Route::delete('/sections/{section}',    [CourseController::class, 'destroySection'])->name('sections.destroy');
    Route::post('/sections/reorder',        [CourseController::class, 'reorderSections'])->name('sections.reorder');

    // Lessons
    Route::get('/sections/{section}/lessons/create',
        [LessonController::class, 'create'])->name('lessons.create');
    Route::post('/sections/{section}/lessons',
        [LessonController::class, 'store'])->name('lessons.store');
    Route::get('/sections/{section}/lessons/{lesson}/edit',
        [LessonController::class, 'edit'])->name('lessons.edit');
    Route::patch('/sections/{section}/lessons/{lesson}',
        [LessonController::class, 'update'])->name('lessons.update');
    Route::delete('/sections/{section}/lessons/{lesson}',
        [LessonController::class, 'destroy'])->name('lessons.destroy');
    Route::post('/sections/{section}/lessons/reorder',
        [LessonController::class, 'reorderLessons'])->name('lessons.reorder');

    // Lesson files
    Route::post('/sections/{section}/lessons/{lesson}/files',
        [LessonController::class, 'uploadFile'])->name('lessons.files.store');
    Route::delete('/sections/{section}/lessons/{lesson}/files/{file}',
        [LessonController::class, 'deleteFile'])->name('lessons.files.destroy');

    // Quiz management
    Route::patch('/sections/{section}/lessons/{lesson}/quiz',
        [LessonController::class, 'updateQuiz'])->name('lessons.quiz.update');
    Route::post('/sections/{section}/lessons/{lesson}/questions',
        [LessonController::class, 'storeQuestion'])->name('lessons.questions.store');
    Route::patch('/sections/{section}/lessons/{lesson}/questions/{question}',
        [LessonController::class, 'updateQuestion'])->name('lessons.questions.update');
    Route::delete('/sections/{section}/lessons/{lesson}/questions/{question}',
        [LessonController::class, 'destroyQuestion'])->name('lessons.questions.destroy');

    // Cohorts
    Route::get('/cohorts',                  [CohortController::class, 'index'])->name('cohorts.index');
    Route::post('/cohorts',                 [CohortController::class, 'store'])->name('cohorts.store');
    Route::get('/cohorts/{cohort}',         [CohortController::class, 'show'])->name('cohorts.show');
    Route::post('/cohorts/{cohort}/enroll', [CohortController::class, 'enroll'])->name('cohorts.enroll');
    Route::delete('/cohorts/{cohort}/enrollments/{enrollmentId}',
        [CohortController::class, 'withdraw'])->name('cohorts.withdraw');

    // Reports
    Route::get('/report', [ReportController::class, 'courseReport'])->name('report');
    Route::get('/report/student/{enrollmentId}',
        [ReportController::class, 'studentReport'])->name('report.student');
});
