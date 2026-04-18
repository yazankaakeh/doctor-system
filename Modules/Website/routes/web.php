<?php

use Illuminate\Support\Facades\Route;
use Modules\Website\Http\Controllers\BlogFrontController;
use Modules\Website\Http\Controllers\DoctorController;
use Modules\Website\Http\Controllers\LandingPageController;
use Modules\Website\Http\Controllers\PageController;
use Modules\Website\Http\Controllers\PortfolioController;
use Modules\Website\Http\Controllers\WebsiteController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('websites', WebsiteController::class)->names('website');

    // Admin sorting screens
    Route::middleware(['can:manage-panels'])->group(function () {
        Route::get('/admin/panels/sort', \Modules\Website\Livewire\Admin\SortPanels::class)->name('admin.panels.sort');
        Route::get('/admin/panels/{panel}/items/sort', \Modules\Website\Livewire\Admin\SortPanelItems::class)->name('admin.panel-items.sort');
    });
});

// Landing Page Routes
Route::middleware('setLocale')->group(function () {
    Route::get('soon', [LandingPageController::class, 'comingSoon'])->name('landing.coming_soon');
    Route::middleware(['coming_soon'])->group(function () {
        Route::get('/', [LandingPageController::class, 'home'])->name('landing.home');
        Route::get('/new-landing', \Modules\Website\Livewire\NewLandingPage::class)->name('website.new-landing');
        Route::get('/privacy', [LandingPageController::class, 'privacy'])->name('landing.privacy');
        Route::get('/info', [LandingPageController::class, 'hiHelloInfo'])->name('landing.hiHelloInfo');
        Route::get('/create', [LandingPageController::class, 'hiHelloCreate'])->name('landing.hiHelloCreate');

        // Blog Routes
        Route::prefix('blog')->name('blog.')->group(function () {
            Route::get('/', [BlogFrontController::class, 'index'])->name('index');
            Route::get('/post/{id}', [BlogFrontController::class, 'show'])->name('show');
            Route::get('/category/{id}', [BlogFrontController::class, 'category'])->name('category');
            Route::get('/tag/{id}', [BlogFrontController::class, 'tag'])->name('tag');
            Route::post('/post/{id}/clap', [BlogFrontController::class, 'clap'])->name('clap');
        });

        // Portfolio Routes
        Route::prefix('portfolio')->name('portfolio.')->group(function () {
            Route::get('/', [PortfolioController::class, 'index'])->name('index');
            Route::get('/{slug}', [PortfolioController::class, 'show'])->name('show');
        });

        // CMS Page Routes
        Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');

        // Doctors Routes
        Route::prefix('doctors')->name('doctors.')->group(function () {
            Route::get('/', [DoctorController::class, 'index'])->name('index');
            Route::get('/{id}', [DoctorController::class, 'show'])->name('show');
        });
    });
});
