<?php

use Illuminate\Support\Facades\Route;
use Modules\Bookings\app\Http\Controllers\DashboardController;
use Modules\Bookings\app\Http\Controllers\BookingController;
use Modules\Bookings\app\Http\Controllers\ResourceController;
use Modules\Bookings\app\Http\Controllers\ServiceController;

Route::get('/dashboard',                      [DashboardController::class, 'index'])->name('dashboard');

Route::resource('bookings',  BookingController::class);
Route::patch('/bookings/{booking}/confirm',   [BookingController::class, 'confirm'])->name('bookings.confirm');
Route::patch('/bookings/{booking}/cancel',    [BookingController::class, 'cancel'])->name('bookings.cancel');
Route::patch('/bookings/{booking}/complete',  [BookingController::class, 'complete'])->name('bookings.complete');

Route::resource('resources', ResourceController::class);
Route::resource('services',  ServiceController::class);
