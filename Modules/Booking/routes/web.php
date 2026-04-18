<?php

use Illuminate\Support\Facades\Route;
use Modules\Booking\Http\Controllers\Doctor\AvailabilityController;
use Modules\Booking\Http\Controllers\Doctor\BookingController as DoctorBookingController;
use Modules\Booking\Http\Controllers\Doctor\RecurringScheduleController;
use Modules\Booking\Http\Controllers\Doctor\ScheduleExceptionController;
use Modules\Booking\Http\Controllers\Patient\BookingController as PatientBookingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public booking page (accessible without authentication)
Route::get('/book-appointment', function () {
    return view('booking::public.book');
})->name('booking.public');

// Doctor routes
Route::prefix('doctor')->name('doctor.')->middleware(['auth:doctor', 'doctorMenu'])->group(function () {
    // Availability management
    Route::prefix('availability')->name('availability.')->group(function () {
        Route::get('/', [AvailabilityController::class, 'index'])->name('index');
        Route::post('/', [AvailabilityController::class, 'store'])->name('store');
        Route::put('/{availability}', [AvailabilityController::class, 'update'])->name('update');
        Route::delete('/{availability}', [AvailabilityController::class, 'destroy'])->name('destroy');
    });

    // Booking management
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [DoctorBookingController::class, 'index'])->name('index');
        Route::get('/{booking}', [DoctorBookingController::class, 'show'])->name('show');
        Route::post('/{booking}/complete', [DoctorBookingController::class, 'complete'])->name('complete');
        Route::post('/{booking}/no-show', [DoctorBookingController::class, 'noShow'])->name('no-show');
    });

    // Recurring schedules management
    Route::prefix('recurring-schedules')->name('recurring-schedules.')->group(function () {
        Route::get('/', [RecurringScheduleController::class, 'index'])->name('index');
        Route::post('/', [RecurringScheduleController::class, 'store'])->name('store');
        Route::put('/{id}', [RecurringScheduleController::class, 'update'])->name('update');
        Route::delete('/{id}', [RecurringScheduleController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [RecurringScheduleController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/generate', [RecurringScheduleController::class, 'generate'])->name('generate');
    });

    // Schedule exceptions management
    Route::prefix('schedule-exceptions')->name('schedule-exceptions.')->group(function () {
        Route::get('/', [ScheduleExceptionController::class, 'index'])->name('index');
        Route::post('/', [ScheduleExceptionController::class, 'store'])->name('store');
        Route::delete('/{id}', [ScheduleExceptionController::class, 'destroy'])->name('destroy');
    });

    // Calendar views
    Route::prefix('calendar')->name('calendar.')->group(function () {
        Route::get('/availability', fn () => view('booking::calendar.availability'))->name('availability');
        Route::get('/appointments', fn () => view('booking::calendar.appointments'))->name('appointments');
    });
});

// Patient routes
Route::prefix('patient')->name('patient.')->middleware(['auth:web', 'patientMenu'])->group(function () {
    // Booking wizard
    Route::prefix('book')->name('book.')->group(function () {
        Route::get('/', [PatientBookingController::class, 'index'])->name('index');
        Route::post('/create', [PatientBookingController::class, 'store'])->name('store');
    });

    // My bookings
    Route::prefix('my-bookings')->name('bookings.')->group(function () {
        Route::get('/', [PatientBookingController::class, 'myBookings'])->name('index');
        Route::get('/{booking}', [PatientBookingController::class, 'show'])->name('show');
        Route::post('/{booking}/cancel', [PatientBookingController::class, 'cancel'])->name('cancel');
    });
});
