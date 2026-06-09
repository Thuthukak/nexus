<?php

use Illuminate\Support\Facades\Route;

// Installation wizard
Route::prefix('install')->name('install.')->group(function () {
    Route::get('/',          [\App\Http\Controllers\Wizard\WizardController::class, 'index'])->name('index');
    Route::get('/step/{step}', [\App\Http\Controllers\Wizard\WizardController::class, 'show'])->name('step');
    Route::post('/step/{step}', [\App\Http\Controllers\Wizard\WizardController::class, 'process'])->name('process');
    Route::get('/check-db',   [\App\Http\Controllers\Wizard\WizardController::class, 'checkDb'])->name('check-db');
    Route::get('/progress',   [\App\Http\Controllers\Wizard\WizardController::class, 'migrationProgress'])->name('progress');
});

// Public event routes — no auth required
Route::prefix('events')->name('events.public.')->group(function () {
    Route::get('/',            [\Modules\Events\app\Http\Controllers\PublicEventController::class, 'index'])->name('index');
    Route::get('/order/{reference}/confirmation', [\Modules\Events\app\Http\Controllers\PublicEventController::class, 'confirmation'])->name('confirmation');
    Route::get('/{slug}',      [\Modules\Events\app\Http\Controllers\PublicEventController::class, 'show'])->name('show');
    Route::post('/{slug}/checkout', [\Modules\Events\app\Http\Controllers\PublicEventController::class, 'checkout'])->name('checkout');
});

// Public quotation routes — no auth required
Route::prefix('quote')->name('quote.')->group(function () {
    Route::get('/{token}',         [\Modules\Financial\app\Http\Controllers\QuotationController::class, 'publicShow'])->name('show');
    Route::post('/{token}/accept', [\Modules\Financial\app\Http\Controllers\QuotationController::class, 'publicAccept'])->name('accept');
    Route::post('/{token}/decline',[\Modules\Financial\app\Http\Controllers\QuotationController::class, 'publicDecline'])->name('decline');
});

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