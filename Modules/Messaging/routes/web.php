<?php

use Illuminate\Support\Facades\Route;
use Modules\Messaging\Http\Controllers\FileUploadController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// File Upload Routes (for messaging attachments)
Route::prefix('messaging')->middleware(['web', 'auth'])->name('messaging.')->group(function () {
    Route::post('/upload', [FileUploadController::class, 'upload'])->name('upload');
    Route::post('/upload/remove', [FileUploadController::class, 'remove'])->name('upload.remove');
});

// Admin routes commented out - not needed for booking chat functionality
// Uncomment and configure proper auth middleware if admin messaging interface is needed
/*
Route::prefix('admin/messaging')->middleware(['web', 'auth:doctor', 'admin-enabled'])->name('messaging.admin.')->group(function () {
    // Agent Dashboard (main inbox)
    Route::get('/', function () {
        return view('messaging::admin.dashboard');
    })->name('dashboard');

    // Conversations
    Route::get('/conversations', function () {
        return view('messaging::admin.conversations.index');
    })->name('conversations.index');

    Route::get('/conversations/{conversation}', function ($conversation) {
        return view('messaging::admin.conversations.show', compact('conversation'));
    })->name('conversations.show');

    // Templates
    Route::get('/templates', function () {
        return view('messaging::admin.templates.index');
    })->name('templates.index');

    // Quick Replies
    Route::get('/quick-replies', function () {
        return view('messaging::admin.quick-replies.index');
    })->name('quick-replies.index');

    // Analytics
    Route::get('/analytics', function () {
        return view('messaging::admin.analytics');
    })->name('analytics');

    // Settings
    Route::get('/settings', function () {
        return view('messaging::admin.settings');
    })->name('settings');
});
*/
