<?php

use Illuminate\Support\Facades\Route;
use Modules\HR\app\Http\Controllers\DashboardController;
use Modules\HR\app\Http\Controllers\EmployeeController;
use Modules\HR\app\Http\Controllers\LeaveController;
use Modules\HR\app\Http\Controllers\DepartmentController;

// HR Settings
Route::get('/settings',   [\Modules\HR\app\Http\Controllers\HrSettingsController::class, 'show'])->name('settings');
Route::patch('/settings', [\Modules\HR\app\Http\Controllers\HrSettingsController::class, 'update'])->name('settings.update');

// Employee Documents
Route::prefix('employees/{employee}/documents')->name('employees.documents.')->group(function () {
    Route::post('/',                  [\Modules\HR\app\Http\Controllers\HrDocumentController::class, 'store'])->name('store');
    Route::get('/{document}/download',[\Modules\HR\app\Http\Controllers\HrDocumentController::class, 'download'])->name('download');
    Route::delete('/{document}',      [\Modules\HR\app\Http\Controllers\HrDocumentController::class, 'destroy'])->name('destroy');
});

// Payslips
Route::prefix('employees/{employee}/payslips')->name('employees.payslips.')->group(function () {
    Route::post('/',                  [\Modules\HR\app\Http\Controllers\PayslipController::class, 'store'])->name('store');
    Route::get('/{payslip}/download', [\Modules\HR\app\Http\Controllers\PayslipController::class, 'download'])->name('download');
    Route::delete('/{payslip}',       [\Modules\HR\app\Http\Controllers\PayslipController::class, 'destroy'])->name('destroy');
});

// My payslips (employee downloading their own)
Route::get('/my-payslips/{payslip}/download',
    [\Modules\HR\app\Http\Controllers\PayslipController::class, 'downloadOwn']
)->name('my-payslips.download');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('employees',   EmployeeController::class);
Route::resource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);

Route::prefix('leave')->name('leave.')->group(function () {
    Route::get('/',                    [LeaveController::class, 'index'])->name('index');
    Route::get('/apply',               [LeaveController::class, 'create'])->name('create');
    Route::post('/',                   [LeaveController::class, 'store'])->name('store');
    Route::get('/{leave}',             [LeaveController::class, 'show'])->name('show');
    Route::patch('/{leave}/approve',   [LeaveController::class, 'approve'])->name('approve');
    Route::patch('/{leave}/reject',    [LeaveController::class, 'reject'])->name('reject');
});
