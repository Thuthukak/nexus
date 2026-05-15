<?php

use Illuminate\Support\Facades\Route;
use Modules\HR\app\Http\Controllers\DashboardController;
use Modules\HR\app\Http\Controllers\EmployeeController;
use Modules\HR\app\Http\Controllers\LeaveController;
use Modules\HR\app\Http\Controllers\DepartmentController;

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
