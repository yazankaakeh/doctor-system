<?php

use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\CategoryController;
use Modules\Blog\Http\Controllers\PostController;
use Modules\Blog\Http\Controllers\QuillUploadController;
use Modules\Blog\Http\Controllers\TagController;

Route::middleware(['auth:doctor', 'audit', 'admin-enabled', 'authorize', 'setLocale', 'doctorMenu'])->name(
    'doctor.',
)->prefix(
    'doctor',
)->group(function () {
    // Route::resource('blogs', BlogController::class)->names('blog');
    Route::resource('category', CategoryController::class)->names('categories');
    Route::resource('posts', PostController::class)->names('posts');
    Route::post('quillUpload/store', [QuillUploadController::class, 'store'])->name('quillUpload.store');

    // Tag routes
    Route::resource('tags', TagController::class)->names('tags');
    Route::post('tags/store-ajax', [TagController::class, 'storeAjax'])->name('tags.storeAjax');
    Route::get('tags-options', [TagController::class, 'getOptions'])->name('tags.options');
});
