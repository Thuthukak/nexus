<?php

use Illuminate\Support\Facades\Route;
use Modules\Financial\app\Http\Controllers\CustomerController;
use Modules\Financial\app\Http\Controllers\DashboardController;
use Modules\Financial\app\Http\Controllers\InvoiceController;
use Modules\Financial\app\Http\Controllers\TaxRateController;

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Invoices
Route::resource('invoices', InvoiceController::class);
Route::prefix('invoices/{invoice}')->name('invoices.')->group(function () {
    Route::patch('/approve',        [InvoiceController::class, 'approve'])->name('approve');
    Route::patch('/mark-sent',      [InvoiceController::class, 'markSent'])->name('mark-sent');
    Route::patch('/cancel',         [InvoiceController::class, 'cancel'])->name('cancel');
    Route::post('/duplicate',       [InvoiceController::class, 'duplicate'])->name('duplicate');
    Route::post('/record-payment',  [InvoiceController::class, 'recordPayment'])->name('record-payment');
});

// Customers
Route::resource('customers', CustomerController::class);

// Tax Rates
Route::get('/tax-rates',              [TaxRateController::class, 'index'])->name('tax-rates.index');
Route::post('/tax-rates',             [TaxRateController::class, 'store'])->name('tax-rates.store');
Route::patch('/tax-rates/{taxRate}',  [TaxRateController::class, 'update'])->name('tax-rates.update');
Route::delete('/tax-rates/{taxRate}', [TaxRateController::class, 'destroy'])->name('tax-rates.destroy');
