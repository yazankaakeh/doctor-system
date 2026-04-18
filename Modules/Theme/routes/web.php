<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\App\Http\Controllers\LoginController;
use Modules\Theme\Http\Controllers\LandingPageController;
use Modules\Theme\Http\Controllers\ThemeController;
use Modules\Theme\Http\Controllers\TinyMCEUploadController;

/* Auth::routes(); */
Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar', 'tr'])) {
        session(['locale' => $locale]);
        App::setLocale($locale);
    }

    return redirect()->back();
})->name('locale');
Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->middleware('doctorMenu')->name(
    'login',
);
Route::middleware('setLocale')->group(function () {
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::resource('theme', ThemeController::class)->names('theme');
    });
    Route::get('soon', [LandingPageController::class, 'comingSoon'])->name('landing.coming_soon');
    Route::middleware(['coming_soon'])->group(function () {
        Route::get('/', [LandingPageController::class, 'home'])->name('landing.home');
        Route::get('/privacy', [LandingPageController::class, 'privacy'])->name('landing.privacy');
        Route::get('/info', [LandingPageController::class, 'hiHelloInfo'])->name('landing.hiHelloInfo');
        Route::get('/create', [LandingPageController::class, 'hiHelloCreate'])->name('landing.hiHelloCreate');
        Route::get('/blog', [LandingPageController::class, 'hiHelloBlog'])->name('landing.hiHelloBlog');
    });

    Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->middleware('doctorMenu')->name(
        'admin.login',
    );
    Route::post('/admin/login/post', [LoginController::class, 'login'])->name('admin.login.submit');
});

// Social login routes are now handled by Auth module

// TinyMCE Image Upload Routes
Route::middleware(['web', 'auth:doctor'])->group(function () {
    Route::post('/uploads/tinymce', [TinyMCEUploadController::class, 'uploadImage'])->name('tinymce.upload');
    Route::post('/uploads/tinymce/multiple', [TinyMCEUploadController::class, 'uploadImages'])->name(
        'tinymce.upload.multiple',
    );
});
