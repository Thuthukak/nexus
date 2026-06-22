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
    Route::get('/',                           [ProfileController::class, 'show'])->name('show');
    Route::patch('/',                         [ProfileController::class, 'update'])->name('update');
    Route::patch('/password',                 [ProfileController::class, 'updatePassword'])->name('password');
    Route::get('/notifications',              [ProfileController::class, 'notificationPreferences'])->name('notifications');
    Route::patch('/notification-preferences', [ProfileController::class, 'updateNotificationPreferences'])->name('notification-preferences');
    Route::get('/payslips',                   [ProfileController::class, 'myPayslips'])->name('payslips');
    Route::get('/payslips/{payslip}/download',[ProfileController::class, 'downloadPayslip'])->name('payslips.download');
    Route::get('/documents/{document}/download',[ProfileController::class, 'downloadDocument'])->name('documents.download');
});

// Settings
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/general',         [SettingsController::class, 'general'])->name('general');
    Route::patch('/general',       [SettingsController::class, 'updateGeneral'])->name('general.update');
    Route::get('/appearance',      [SettingsController::class, 'appearance'])->name('appearance');
    Route::patch('/appearance',    [SettingsController::class, 'updateAppearance'])->name('appearance.update');
});

// Activity Log
Route::prefix('activity')->name('activity.')->group(function () {
    Route::get('/',                    [\Modules\Core\app\Http\Controllers\ActivityLogController::class, 'index'])->name('index');
    Route::get('/{type}/{id}',         [\Modules\Core\app\Http\Controllers\ActivityLogController::class, 'forSubject'])->name('subject');
});

// Notifications (JSON — called by polling store)
Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/',              [\Modules\Core\app\Http\Controllers\NotificationController::class, 'index'])->name('index');
    Route::patch('/{id}/read',   [\Modules\Core\app\Http\Controllers\NotificationController::class, 'markRead'])->name('read');
    Route::patch('/read-all',    [\Modules\Core\app\Http\Controllers\NotificationController::class, 'markAllRead'])->name('read-all');
    Route::delete('/{id}',       [\Modules\Core\app\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
});

// User Management
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/',                    [\Modules\Core\app\Http\Controllers\UserController::class, 'index'])->name('index');
    Route::get('/create',              [\Modules\Core\app\Http\Controllers\UserController::class, 'create'])->name('create');
    Route::post('/',                   [\Modules\Core\app\Http\Controllers\UserController::class, 'store'])->name('store');
    Route::get('/{user}',              [\Modules\Core\app\Http\Controllers\UserController::class, 'show'])->name('show');
    Route::get('/{user}/edit',         [\Modules\Core\app\Http\Controllers\UserController::class, 'edit'])->name('edit');
    Route::patch('/{user}',            [\Modules\Core\app\Http\Controllers\UserController::class, 'update'])->name('update');
    Route::patch('/{user}/deactivate', [\Modules\Core\app\Http\Controllers\UserController::class, 'deactivate'])->name('deactivate');
    Route::patch('/{user}/activate',   [\Modules\Core\app\Http\Controllers\UserController::class, 'activate'])->name('activate');
    Route::post('/{user}/reset-password', [\Modules\Core\app\Http\Controllers\UserController::class, 'resetPassword'])->name('reset-password');
    Route::delete('/{user}',           [\Modules\Core\app\Http\Controllers\UserController::class, 'destroy'])->name('destroy');
});

// Roles
Route::prefix('roles')->name('roles.')->group(function () {
    Route::get('/',              [\Modules\Core\app\Http\Controllers\RoleController::class, 'index'])->name('index');
    Route::post('/',             [\Modules\Core\app\Http\Controllers\RoleController::class, 'store'])->name('store');
    Route::patch('/{role}',      [\Modules\Core\app\Http\Controllers\RoleController::class, 'update'])->name('update');
    Route::delete('/{role}',     [\Modules\Core\app\Http\Controllers\RoleController::class, 'destroy'])->name('destroy');
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
Route::post('/settings/logo/icon', [\Modules\Core\app\Http\Controllers\LogoController::class, 'uploadIcon'])->name('settings.logo.icon.upload');
Route::delete('/settings/logo/icon', [\Modules\Core\app\Http\Controllers\LogoController::class, 'destroyIcon'])->name('settings.logo.icon.destroy');
