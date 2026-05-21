<?php

use Illuminate\Support\Facades\Route;
use Modules\Financial\app\Http\Controllers\CustomerController;
use Modules\Financial\app\Http\Controllers\DashboardController;
use Modules\Financial\app\Http\Controllers\InvoiceController;
use Modules\Financial\app\Http\Controllers\RecurringInvoiceController;
use Modules\Financial\app\Http\Controllers\TaxRateController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Invoices
Route::resource('invoices', InvoiceController::class);

Route::prefix('invoices/{invoice}')->name('invoices.')->group(function () {
    Route::patch('/approve',            [InvoiceController::class, 'approve'])->name('approve');
    Route::patch('/mark-sent',          [InvoiceController::class, 'markSent'])->name('mark-sent');
    Route::post('/send',                [InvoiceController::class, 'send'])->name('send');
    Route::patch('/cancel',             [InvoiceController::class, 'cancel'])->name('cancel');
    Route::post('/duplicate',           [InvoiceController::class, 'duplicate'])->name('duplicate');
    Route::post('/record-payment',      [InvoiceController::class, 'recordPayment'])->name('record-payment');
    Route::get('/download-pdf',         [InvoiceController::class, 'downloadPdf'])->name('download-pdf');
    Route::post('/send-receipt',        [InvoiceController::class, 'sendReceipt'])->name('send-receipt');
    Route::get('/download-receipt',     [InvoiceController::class, 'downloadReceipt'])->name('download-receipt');
    Route::post('/make-recurring',      [RecurringInvoiceController::class, 'store'])->name('make-recurring');
});

// Recurring schedules
Route::prefix('recurring')->name('recurring.')->group(function () {
    Route::get('/',                                        [RecurringInvoiceController::class, 'index'])->name('index');
    Route::patch('/{recurringInvoice}/pause',              [RecurringInvoiceController::class, 'pause'])->name('pause');
    Route::patch('/{recurringInvoice}/resume',             [RecurringInvoiceController::class, 'resume'])->name('resume');
    Route::patch('/{recurringInvoice}/cancel',             [RecurringInvoiceController::class, 'cancel'])->name('cancel');
    Route::delete('/{recurringInvoice}',                   [RecurringInvoiceController::class, 'destroy'])->name('destroy');
});

// Customers
Route::resource('customers', CustomerController::class);

// Tax Rates
Route::get('/tax-rates',              [TaxRateController::class, 'index'])->name('tax-rates.index');
Route::post('/tax-rates',             [TaxRateController::class, 'store'])->name('tax-rates.store');
Route::patch('/tax-rates/{taxRate}',  [TaxRateController::class, 'update'])->name('tax-rates.update');
Route::delete('/tax-rates/{taxRate}', [TaxRateController::class, 'destroy'])->name('tax-rates.destroy');

// Payment settings
Route::get('/settings/payments',   [\Modules\Financial\app\Http\Controllers\PaymentSettingsController::class, 'show'])->name('settings.payments');
Route::patch('/settings/payments', [\Modules\Financial\app\Http\Controllers\PaymentSettingsController::class, 'update'])->name('settings.payments.update');

// Internal JSON API
Route::prefix('api')->name('api.')->group(function () {
    Route::post('/customers',  [\Modules\Financial\app\Http\Controllers\Api\CustomerApiController::class, 'store'])->name('customers.store');
    Route::get('/products',    [\Modules\Financial\app\Http\Controllers\Api\ProductApiController::class, 'index'])->name('products.index');
    Route::post('/products',   [\Modules\Financial\app\Http\Controllers\Api\ProductApiController::class, 'store'])->name('products.store');
});
