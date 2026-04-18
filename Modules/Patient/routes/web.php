<?php

use Illuminate\Support\Facades\Route;
use Modules\Patient\Http\Controllers\AppointmentController;
use Modules\Patient\Http\Controllers\DashboardController;
use Modules\Patient\Http\Controllers\MedicalHistoryController;
use Modules\Patient\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Patient Module Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:web', 'setLocale', 'patientMenu'])
    ->name('patient.')
    ->prefix('patient')
    ->group(function () {
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Appointments
        Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('appointments/{id}', [AppointmentController::class, 'show'])->name('appointments.show');
        Route::post('appointments/{appointmentId}/tests/{testPivotId}/upload', [AppointmentController::class, 'uploadTestResult'])->name('appointments.tests.upload');
        Route::get('appointments/{id}/prescription/download', [AppointmentController::class, 'downloadPrescription'])->name('appointments.prescription.download');

        // Medical History
        Route::get('medical-history', [MedicalHistoryController::class, 'index'])->name('medical-history.index');

        // Profile
        Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');
    });
