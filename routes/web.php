<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('core.dashboard');
});

// Auth routes handled by Fortify automatically
// Dashboard handled by Core module RouteServiceProvider
