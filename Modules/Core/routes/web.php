<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\app\Http\Controllers\DashboardController;
use Modules\Core\app\Http\Controllers\ProfileController;
use Modules\Core\app\Http\Controllers\SettingsController;

// Dashboard
Route::get('/dashboard', function () {
    return inertia('Core/Pages/Dashboard');
})->name('dashboard');

// Profile
Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/',                [ProfileController::class, 'show'])->name('show');
    Route::patch('/',              [ProfileController::class, 'update'])->name('update');
    Route::patch('/password',      [ProfileController::class, 'updatePassword'])->name('password');
});

// Settings
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/general',         [SettingsController::class, 'general'])->name('general');
    Route::patch('/general',       [SettingsController::class, 'updateGeneral'])->name('general.update');
    Route::get('/appearance',      [SettingsController::class, 'appearance'])->name('appearance');
    Route::patch('/appearance',    [SettingsController::class, 'updateAppearance'])->name('appearance.update');
});
