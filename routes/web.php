<?php

use Illuminate\Support\Facades\Route;

// Installation wizard
Route::prefix('install')->name('install.')->group(function () {
    Route::get('/',          [\App\Http\Controllers\Wizard\WizardController::class, 'index'])->name('index');
    Route::get('/step/{step}', [\App\Http\Controllers\Wizard\WizardController::class, 'show'])->name('step');
    Route::post('/step/{step}', [\App\Http\Controllers\Wizard\WizardController::class, 'process'])->name('process');
    Route::get('/debug-state',   [\App\Http\Controllers\Wizard\WizardController::class, 'debugState'])->name('debug');
    Route::get('/migration-progress', [\App\Http\Controllers\Wizard\WizardController::class, 'migrationProgress'])->name('migration.progress');
    Route::get('/check-db',   [\App\Http\Controllers\Wizard\WizardController::class, 'checkDb'])->name('check-db');
    Route::get('/progress',   [\App\Http\Controllers\Wizard\WizardController::class, 'migrationProgress'])->name('progress');
});

// ── Internal Auth ─────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',          [\App\Http\Controllers\Auth\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',         [\App\Http\Controllers\Auth\AuthController::class, 'login'])->name('login.post');

    // Forgot password
    Route::get('/forgot-password',  [\App\Http\Controllers\Auth\AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\AuthController::class, 'sendResetLink'])->name('password.email');

    // Reset password
    Route::get('/reset-password/{token}',  [\App\Http\Controllers\Auth\AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password',         [\App\Http\Controllers\Auth\AuthController::class, 'resetPassword'])->name('password.update');

    // Accept invite (signed URL — no guest middleware needed but fine here)
    Route::get('/accept-invite/{user}',   [\App\Http\Controllers\Auth\AuthController::class, 'showAcceptInvite'])->name('auth.accept-invite');
    Route::post('/accept-invite/{user}',  [\App\Http\Controllers\Auth\AuthController::class, 'acceptInvite'])->name('auth.accept-invite.post');
});

Route::post('/logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');

// Resend invite — admin only
Route::post('/users/{user}/resend-invite',
    [\App\Http\Controllers\Auth\AuthController::class, 'resendInvite']
)->middleware(['web', 'auth'])->name('users.resend-invite');

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