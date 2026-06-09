<?php

use Illuminate\Support\Facades\Route;
use Modules\Events\app\Http\Controllers\EventController;

// Admin routes (auth required, lms prefix handled by ServiceProvider)
Route::resource('events', EventController::class);

Route::prefix('events/{event}')->name('events.')->group(function () {
    Route::get('/orders',                   [EventController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}/download',  [EventController::class, 'downloadTickets'])->name('orders.download');

    // Ticket types
    Route::post('/ticket-types',            [EventController::class, 'storeTicketType'])->name('ticket-types.store');
    Route::patch('/ticket-types/{ticketType}', [EventController::class, 'updateTicketType'])->name('ticket-types.update');
    Route::delete('/ticket-types/{ticketType}',[EventController::class, 'destroyTicketType'])->name('ticket-types.destroy');
});
