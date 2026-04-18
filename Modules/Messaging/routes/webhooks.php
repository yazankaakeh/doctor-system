<?php

use Illuminate\Support\Facades\Route;
use Modules\Messaging\Http\Controllers\Webhooks\TelegramWebhookController;
use Modules\Messaging\Http\Controllers\Webhooks\WhatsAppWebhookController;

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
|
| These routes handle incoming webhooks from messaging platforms.
| They should not have CSRF protection.
|
*/

Route::prefix('webhooks/messaging')->name('messaging.webhooks.')->group(function () {
    // WhatsApp webhooks (Meta)
    Route::get('/whatsapp', [WhatsAppWebhookController::class, 'verify'])->name('whatsapp.verify');
    Route::post('/whatsapp', [WhatsAppWebhookController::class, 'handle'])->name('whatsapp.handle');

    // Telegram webhooks
    Route::post('/telegram', [TelegramWebhookController::class, 'handle'])->name('telegram.handle');
});
