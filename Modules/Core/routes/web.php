<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\app\Http\Controllers\ContactUsController;
use Modules\Core\app\Http\Controllers\EnvController;
use Modules\Core\App\Http\Controllers\SecureFileController;
use Modules\Core\App\Http\Controllers\ThemeSettingsController;
use Modules\Core\Http\Controllers\VideoConsultationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Doctor environment settings routes
Route::middleware(['auth:doctor', 'doctorMenu', 'admin-enabled', 'setLocale', 'authorize', 'audit'])->name(
    'doctor.',
)->prefix(
    'doctor',
)->group(function () {
    Route::get('env', [EnvController::class, 'index'])->name('env.get');
    Route::post('env/update', [EnvController::class, 'update'])->name('env.update');
    Route::post('env/sendTestEmail', [EnvController::class, 'sendTestEmail'])->name('env.sendTestEmail');

    // Firebase routes
    Route::post('firebase/saveToken', [EnvController::class, 'savePushToken'])->name('firebase.saveToken');
    Route::post('firebase/sendTestNotification', [EnvController::class, 'sendTestNotification'])->name(
        'firebase.sendTestNotification',
    );

    // Theme Settings routes
    Route::get('theme-settings', [ThemeSettingsController::class, 'index'])->name('theme.settings.index');
    Route::post('theme-settings/update', [ThemeSettingsController::class, 'update'])->name('theme.settings.update');
    Route::post('theme-settings/reset', [ThemeSettingsController::class, 'reset'])->name('theme.settings.reset');
});
Route::post('submitContactUs', [ContactUsController::class, 'submitContactForm'])->name('env.submitContactForm');

/*
|--------------------------------------------------------------------------
| Video Consultation Routes
|--------------------------------------------------------------------------
|
| These routes handle video consultation functionality using Jitsi or other
| video providers. Routes are protected by authentication middleware.
|
*/
Route::middleware(['auth:doctor,patient,web', 'setLocale'])->prefix('video')->name('video.')->group(function () {
    // Join a video consultation room
    Route::get('join/{roomName}', [VideoConsultationController::class, 'join'])->name('join');

    // Create a new meeting room (for authenticated users)
    Route::post('create', [VideoConsultationController::class, 'create'])->name('create');

    // Get room information (API)
    Route::get('info/{roomName}', [VideoConsultationController::class, 'info'])->name('info');
});

/*
|--------------------------------------------------------------------------
| Secure File Download Routes
|--------------------------------------------------------------------------
|
| These routes handle secure file downloads for medical documents, payment
| proofs, and meeting recordings. Access is restricted based on user role
| and ownership.
|
*/
Route::middleware(['auth:doctor,patient,web', 'setLocale'])->prefix('secure-file')->name('secure-file.')->group(function () {
    Route::get('download/{mediaId}', [SecureFileController::class, 'download'])->name('download')
        ->where('mediaId', '[0-9]+');
});
