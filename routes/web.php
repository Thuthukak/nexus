<?php

use Illuminate\Support\Facades\Route;

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