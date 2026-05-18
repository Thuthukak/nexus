<?php

use Illuminate\Support\Facades\Route;
use Modules\Financial\app\Http\Controllers\CustomerController;
use Modules\Financial\app\Http\Controllers\DashboardController;
use Modules\Financial\app\Http\Controllers\InvoiceController;
use Modules\Financial\app\Http\Controllers\TaxRateController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('invoices', InvoiceController::class);
Route::prefix('invoices/{invoice}')->name('invoices.')->group(function () {
    Route::patch('/approve',        [InvoiceController::class, 'approve'])->name('approve');
    Route::patch('/mark-sent',      [InvoiceController::class, 'markSent'])->name('mark-sent');
    Route::post('/send',            [InvoiceController::class, 'send'])->name('send');
    Route::patch('/cancel',         [InvoiceController::class, 'cancel'])->name('cancel');
    Route::post('/duplicate',       [InvoiceController::class, 'duplicate'])->name('duplicate');
    Route::post('/record-payment',  [InvoiceController::class, 'recordPayment'])->name('record-payment');
    Route::get('/download-pdf',     [InvoiceController::class, 'downloadPdf'])->name('download-pdf');
});

Route::resource('customers', CustomerController::class);

Route::get('/tax-rates',              [TaxRateController::class, 'index'])->name('tax-rates.index');
Route::post('/tax-rates',             [TaxRateController::class, 'store'])->name('tax-rates.store');
Route::patch('/tax-rates/{taxRate}',  [TaxRateController::class, 'update'])->name('tax-rates.update');
Route::delete('/tax-rates/{taxRate}', [TaxRateController::class, 'destroy'])->name('tax-rates.destroy');

// Internal JSON API — used by inline form creation
Route::prefix('api')->name('api.')->group(function () {
    Route::post('/customers',       [\Modules\Financial\app\Http\Controllers\Api\CustomerApiController::class, 'store'])->name('customers.store');
    Route::get('/products',         [\Modules\Financial\app\Http\Controllers\Api\ProductApiController::class, 'index'])->name('products.index');
    Route::post('/products',        [\Modules\Financial\app\Http\Controllers\Api\ProductApiController::class, 'store'])->name('products.store');
});
