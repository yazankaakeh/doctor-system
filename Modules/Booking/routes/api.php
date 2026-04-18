<?php

use Illuminate\Support\Facades\Route;
use Modules\Booking\Http\Controllers\Api\CalendarController;
use Modules\Booking\Http\Controllers\BookingController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('bookings', BookingController::class)->names('booking');

    // Calendar API endpoints
    Route::prefix('calendar')->name('calendar.')->group(function () {
        Route::get('/availabilities', [CalendarController::class, 'availabilities'])->name('availabilities');
        Route::get('/bookings', [CalendarController::class, 'bookings'])->name('bookings');
    });
});
