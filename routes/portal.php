<?php

use App\Http\Controllers\Portal\PortalAuthController;
use App\Http\Controllers\Portal\PortalBookingController;
use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalInvoiceController;
use App\Http\Controllers\Portal\PortalProfileController;
use App\Http\Controllers\Portal\PortalQuotationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Portal Routes
|--------------------------------------------------------------------------
*/

// Unauthenticated portal routes
Route::prefix('portal')->name('portal.')->group(function () {

    Route::get('/login',  [PortalAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [PortalAuthController::class, 'login'])->name('login.post');

    // Invite acceptance (signed URL)
    Route::get('/accept-invite/{user}',
        [PortalAuthController::class, 'showAcceptInvite']
    )->name('accept-invite');

    Route::post('/accept-invite/{user}',
        [PortalAuthController::class, 'acceptInvite']
    )->name('accept-invite.post');
});

// Authenticated portal routes
Route::prefix('portal')->name('portal.')->middleware(['web', 'customer.portal'])->group(function () {

    Route::post('/logout', [PortalAuthController::class, 'logout'])->name('logout');

    Route::get('/',          fn () => redirect()->route('portal.dashboard'));
    Route::get('/dashboard', [PortalDashboardController::class, 'index'])->name('dashboard');

    // Invoices
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/',         [PortalInvoiceController::class, 'index'])->name('index');
        Route::get('/{invoice}',[PortalInvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/pdf', [PortalInvoiceController::class, 'downloadPdf'])->name('pdf');
    });

    // Quotations
    Route::prefix('quotations')->name('quotations.')->group(function () {
        Route::get('/',            [PortalQuotationController::class, 'index'])->name('index');
        Route::get('/{quotation}', [PortalQuotationController::class, 'show'])->name('show');
    });

    // Bookings
    Route::get('/bookings', [PortalBookingController::class, 'index'])->name('bookings');

    // Documents
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/',              [\App\Http\Controllers\Portal\PortalDocumentController::class, 'index'])->name('index');
        Route::get('/{document}/download', [\App\Http\Controllers\Portal\PortalDocumentController::class, 'download'])->name('download');
    });

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',         [PortalProfileController::class, 'show'])->name('show');
        Route::patch('/',       [PortalProfileController::class, 'update'])->name('update');
        Route::patch('/password',[PortalProfileController::class, 'updatePassword'])->name('password');
    });
});
