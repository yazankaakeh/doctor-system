<?php

/*Route::get('admin/dashboard', function () {
    return view('sales-dashboard');
})->name('admin.dashboard')->middleware(['auth:admin', 'admin-enabled']);*/

/*
Route::get('/', function () {
    return view('theme::user.auth.login');
})->name('login');

Route::post('admin/login', [AuthController::class, 'login'])
    ->name('admin.login.post');
*/

use Modules\AdminManagement\Http\Controllers\AuthController;
use Modules\AdminManagement\Http\Controllers\UserAuthController;

Route::post('admin/logout', [AuthController::class, 'logout'])
    ->name('admin.logout.post');
Route::post('user/logout', [UserAuthController::class, 'logout'])
    ->name('user.logout.post');
