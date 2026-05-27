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

// Module Manager
Route::prefix('admin/modules')->name('modules.')->group(function () {
    Route::get('/',                    [\Modules\Core\app\Http\Controllers\ModuleManagerController::class, 'index'])->name('index');
    Route::patch('/{module}/enable',   [\Modules\Core\app\Http\Controllers\ModuleManagerController::class, 'enable'])->name('enable');
    Route::patch('/{module}/disable',  [\Modules\Core\app\Http\Controllers\ModuleManagerController::class, 'disable'])->name('disable');
    Route::post('/licence',            [\Modules\Core\app\Http\Controllers\ModuleManagerController::class, 'updateLicence'])->name('licence.update');
});

// Logo
Route::post('/settings/logo',   [\Modules\Core\app\Http\Controllers\LogoController::class, 'upload'])->name('settings.logo.upload');
Route::delete('/settings/logo', [\Modules\Core\app\Http\Controllers\LogoController::class, 'destroy'])->name('settings.logo.destroy');
