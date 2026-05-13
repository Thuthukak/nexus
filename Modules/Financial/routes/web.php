<?php

use Illuminate\Support\Facades\Route;
use Modules\Financial\app\Http\Controllers\DashboardController;
use Modules\Financial\app\Http\Controllers\InvoiceController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('invoices', InvoiceController::class);
