<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Patient\ForgotPasswordController;
use Modules\Auth\Http\Controllers\Patient\LoginController;
use Modules\Auth\Http\Controllers\Patient\RegisterController;
use Modules\Auth\Http\Controllers\Patient\ResetPasswordController;
use Modules\Auth\Http\Controllers\Patient\VerifyEmailController;
use Modules\Auth\Http\Controllers\SocialController;
use Modules\Auth\Http\Middleware\RedirectIfAuthenticatedPatient;

/*
|--------------------------------------------------------------------------
| Auth Module Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for the Auth module.
|
*/

// Social Login Routes
Route::get('/auth/{provider}/redirect', [SocialController::class, 'redirect'])
    ->whereIn('provider', ['google', 'facebook', 'x'])
    ->name('auth.social.redirect');

Route::get('/auth/{provider}/callback', [SocialController::class, 'callback'])
    ->whereIn('provider', ['google', 'facebook', 'x'])
    ->name('auth.social.callback');

/*
|--------------------------------------------------------------------------
| Patient Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'setLocale'])->group(function () {
    // Guest routes (not authenticated as patient)
    Route::middleware([RedirectIfAuthenticatedPatient::class])->group(function () {
        // Login
        Route::get('/patient/login', [LoginController::class, 'showLoginForm'])->name('patient.login');
        Route::post('/patient/login', [LoginController::class, 'login'])->name('patient.login.post');

        // Registration
        Route::get('/patient/register', [RegisterController::class, 'showRegistrationForm'])->name('patient.register');
        Route::post('/patient/register', [RegisterController::class, 'register'])->name('patient.register.post');

        // Password Reset
        Route::get('/patient/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('patient.password.request');
        Route::post('/patient/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('patient.password.email');
        Route::get('/patient/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('patient.password.reset');
        Route::post('/patient/password/reset', [ResetPasswordController::class, 'reset'])->name('patient.password.update');
    });

    // Authenticated patient routes
    Route::middleware(['auth:web'])->group(function () {
        // Logout
        Route::post('/patient/logout', [LoginController::class, 'logout'])->name('patient.logout');

        // Email Verification
        Route::get('/patient/email/verify', [VerifyEmailController::class, 'show'])->name('patient.verification.notice');
        Route::get('/patient/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('patient.verification.verify');
        Route::post('/patient/email/verification-notification', [VerifyEmailController::class, 'resend'])
            ->middleware('throttle:6,1')
            ->name('patient.verification.send');
    });
});

/*
|--------------------------------------------------------------------------
| Doctor Authentication Routes
|--------------------------------------------------------------------------
*/

use Modules\Auth\Http\Controllers\Doctor\ForgotPasswordController as DoctorForgotPasswordController;
use Modules\Auth\Http\Controllers\Doctor\LoginController as DoctorLoginController;
use Modules\Auth\Http\Controllers\Doctor\RegisterController as DoctorRegisterController;
use Modules\Auth\Http\Controllers\Doctor\ResetPasswordController as DoctorResetPasswordController;
use Modules\Auth\Http\Middleware\RedirectIfAuthenticatedDoctor;

Route::middleware(['web', 'setLocale'])->group(function () {
    // Guest routes (not authenticated as doctor)
    Route::middleware([RedirectIfAuthenticatedDoctor::class])->group(function () {
        // Login
        Route::get('/doctor/login', [DoctorLoginController::class, 'showLoginForm'])->name('doctor.login');
        Route::post('/doctor/login', [DoctorLoginController::class, 'login'])->name('doctor.login.post');

        // Registration
        Route::get('/doctor/register', [DoctorRegisterController::class, 'showRegistrationForm'])->name('doctor.register');
        Route::post('/doctor/register', [DoctorRegisterController::class, 'register'])->name('doctor.register.post');

        // Password Reset
        Route::get('/doctor/password/reset', [DoctorForgotPasswordController::class, 'showLinkRequestForm'])->name('doctor.password.request');
        Route::post('/doctor/password/email', [DoctorForgotPasswordController::class, 'sendResetLinkEmail'])->name('doctor.password.email');
        Route::get('/doctor/password/reset/{token}', [DoctorResetPasswordController::class, 'showResetForm'])->name('doctor.password.reset');
        Route::post('/doctor/password/reset', [DoctorResetPasswordController::class, 'reset'])->name('doctor.password.update');
    });

    // Authenticated doctor routes
    Route::middleware(['auth:doctor'])->group(function () {
        // Logout
        Route::post('/doctor/logout', [DoctorLoginController::class, 'logout'])->name('doctor.logout');
    });
});

/*
|--------------------------------------------------------------------------
| Default Login Route
|--------------------------------------------------------------------------
|
| Laravel's auth middleware looks for a route named 'login' by default.
| Since 'web' and 'patient' guards are for patients, we alias patient.login
| as the default login route.
|
*/
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
