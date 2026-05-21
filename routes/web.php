<?php

use Illuminate\Support\Facades\Route;

// Public payment routes — no authentication required
Route::prefix('pay')->name('pay.')->group(function () {
    Route::get('/{token}',           [\App\Http\Controllers\PaymentController::class, 'show'])->name('show');
    Route::get('/{token}/initiate', [\App\Http\Controllers\PaymentController::class, 'initiate'])->name('initiate');
    Route::get('/{token}/return',    [\App\Http\Controllers\PaymentController::class, 'handleReturn'])->name('return');
    Route::get('/{token}/cancel',    [\App\Http\Controllers\PaymentController::class, 'handleCancel'])->name('cancel');
});

// PayFast ITN webhook (no CSRF — excluded below)
Route::post('/webhooks/payfast',  [\App\Http\Controllers\WebhookController::class, 'payfast'])->name('webhooks.payfast');
Route::post('/webhooks/paystack', [\App\Http\Controllers\WebhookController::class, 'paystack'])->name('webhooks.paystack');

Route::get('/', function () {
    return redirect()->route('core.dashboard');
});

// Auth routes handled by Fortify automatically
// Dashboard handled by Core module RouteServiceProvider

// Temporary toast test route — remove before production
// Route::get('/test-toast', function () {
//     return redirect('/dashboard')->with('toast', [
//         'type'    => 'success',
//         'title'   => 'Toast system working',
//         'message' => 'Flash messages are wired up correctly.',
//     ]);
// });