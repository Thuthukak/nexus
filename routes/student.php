<?php

use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentLearningController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student Learning Portal Routes
|--------------------------------------------------------------------------
| Uses the 'web' middleware + auth (internal guard).
| Students log in at the normal /login page and are
| redirected here based on their role.
*/

Route::prefix('student')->name('student.')->middleware(['web', 'auth'])->group(function () {

    Route::get('/',          fn () => redirect()->route('student.dashboard'));
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

    Route::prefix('learn/{enrollmentId}')->name('learn.')->group(function () {
        Route::get('/',                          [StudentLearningController::class, 'course'])->name('course');
        Route::get('/lesson/{lessonId}',          [StudentLearningController::class, 'lesson'])->name('lesson');
        Route::post('/lesson/{lessonId}/complete',[StudentLearningController::class, 'markComplete'])->name('complete');
        Route::post('/lesson/{lessonId}/quiz',    [StudentLearningController::class, 'submitQuiz'])->name('quiz');
        Route::get('/files/{fileId}',             [StudentLearningController::class, 'downloadFile'])->name('file');
        Route::get('/certificate',                [StudentLearningController::class, 'downloadCertificate'])->name('certificate');
    });
});
