<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Core Module Web Routes
|--------------------------------------------------------------------------
| Admin and settings routes for the Core module.
| Prefix: none (admin routes will use /admin prefix)
*/

Route::get('/dashboard', function () {
    return inertia('Core/Pages/Dashboard');
})->name('dashboard');
