<?php

use Illuminate\Support\Facades\Route;
use Modules\LMS\app\Http\Controllers\Student\StudentCourseController;
use Modules\LMS\app\Http\Controllers\Student\StudentDashboardController;

Route::get('/dashboard',  [StudentDashboardController::class, 'index'])->name('dashboard');

Route::prefix('/courses/{enrollmentId}')->name('course.')->group(function () {
    Route::get('/',                              [StudentCourseController::class, 'show'])->name('show');
    Route::get('/lessons/{lessonId}',            [StudentCourseController::class, 'lesson'])->name('lesson');
    Route::post('/lessons/{lessonId}/complete',  [StudentCourseController::class, 'completeLesson'])->name('lesson.complete');
    Route::get('/lessons/{lessonId}/quiz',       [StudentCourseController::class, 'showQuiz'])->name('quiz');
    Route::post('/lessons/{lessonId}/quiz',      [StudentCourseController::class, 'submitQuiz'])->name('quiz.submit');
    Route::get('/files/{fileId}',                [StudentCourseController::class, 'downloadFile'])->name('file.download');
    Route::get('/certificate',                   [StudentCourseController::class, 'downloadCertificate'])->name('certificate');
    Route::get('/assignments/{assignmentId}',    [StudentCourseController::class, 'showAssignment'])->name('assignment');
    Route::post('/assignments/{assignmentId}',   [StudentCourseController::class, 'submitAssignment'])->name('assignment.submit');
});
