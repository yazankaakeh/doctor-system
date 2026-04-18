<?php

use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\Admin\PaymentController;
use Modules\Payment\Http\Controllers\CreditCardController;
use Modules\Payment\Http\Controllers\Doctor\PaymentController as DoctorPaymentController;
use Modules\Payment\Http\Controllers\OfflinePaymentController;
use Modules\Payment\Http\Controllers\PayPalController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
*/

// PayPal routes for patients
Route::prefix('payment/paypal')->name('payment.paypal.')->middleware(['web', 'patientMenu', 'auth:web', 'setLocale'])->group(function () {
    Route::post('/create/{booking}', [PayPalController::class, 'create'])->name('create');
    Route::get('/success', [PayPalController::class, 'success'])->name('success');
    Route::get('/cancel', [PayPalController::class, 'cancel'])->name('cancel');
});

// Offline payment routes for patients
Route::prefix('payment/offline')->name('payment.offline.')->middleware(['web', 'patientMenu', 'auth:web', 'setLocale'])->group(function () {
    Route::get('/{booking}', [OfflinePaymentController::class, 'show'])->name('show');
    Route::post('/{booking}', [OfflinePaymentController::class, 'submit'])->name('submit');
});

// Credit-card payment (prototype) for patients
Route::prefix('payment/credit-card')->name('payment.credit_card.')->middleware(['web', 'patientMenu', 'auth:web', 'setLocale'])->group(function () {
    Route::get('/{booking}', [CreditCardController::class, 'show'])->name('show');
    Route::post('/{booking}', [CreditCardController::class, 'submit'])->name('submit');
});

// Admin payment routes
Route::prefix('admin/payments')->name('admin.payment.')->middleware(['auth', 'can:manage payments'])->group(function () {
    Route::get('/', [PaymentController::class, 'index'])->name('index');
    Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
    Route::post('/{payment}/verify', [PaymentController::class, 'verify'])->name('verify');
    Route::post('/{payment}/reject', [PaymentController::class, 'reject'])->name('reject');
});

// Doctor payment routes
Route::prefix('doctor/payments')->name('doctor.payment.')->middleware(['web', 'doctorMenu', 'auth:doctor', 'setLocale'])->group(function () {
    Route::get('/', [DoctorPaymentController::class, 'index'])->name('index');
    Route::get('/{payment}', [DoctorPaymentController::class, 'show'])->name('show');
    Route::post('/{payment}/verify', [DoctorPaymentController::class, 'verify'])->name('verify');
    Route::post('/{payment}/reject', [DoctorPaymentController::class, 'reject'])->name('reject');
});
