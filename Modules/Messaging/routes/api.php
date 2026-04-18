<?php

use Illuminate\Support\Facades\Route;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Http\Controllers\WebChatController;
use Modules\Messaging\Services\MessagingService;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('messaging')->name('messaging.api.')->group(function () {
    // WebChat API (public, for website widget)
    Route::prefix('webchat')->name('webchat.')->group(function () {
        Route::post('/init', [WebChatController::class, 'init'])->name('init');
        Route::post('/send', [WebChatController::class, 'sendMessage'])->name('send');
        Route::get('/messages', [WebChatController::class, 'getMessages'])->name('messages');
        Route::post('/read', [WebChatController::class, 'markAsRead'])->name('read');
        Route::post('/end', [WebChatController::class, 'endSession'])->name('end');
    });

    // Authenticated API endpoints
    Route::middleware(['auth:sanctum'])->group(function () {
        // Conversations
        Route::get('/conversations', function () {
            $service = app(MessagingService::class);
            $conversations = $service->getAssignedConversations(auth()->id());

            return response()->json(['data' => $conversations]);
        })->name('conversations.index');

        Route::get('/conversations/{conversation}', function ($conversationId) {
            $service = app(MessagingService::class);
            $conversation = $service->getConversation($conversationId);

            if (! $conversation) {
                return response()->json(['error' => 'Not found'], 404);
            }

            return response()->json(['data' => $conversation]);
        })->name('conversations.show');

        // Messages
        Route::get('/conversations/{conversation}/messages', function ($conversationId) {
            $service = app(MessagingService::class);
            $conversation = $service->getConversation($conversationId);

            if (! $conversation) {
                return response()->json(['error' => 'Not found'], 404);
            }

            $messages = $service->getMessages($conversation, request('limit', 50));

            return response()->json(['data' => $messages]);
        })->name('messages.index');

        // Unread count
        Route::get('/unread-count', function () {
            $service = app(MessagingService::class);

            return response()->json([
                'count' => $service->getUnreadCount(auth()->id()),
            ]);
        })->name('unread-count');

        // Quick replies
        Route::get('/quick-replies', function () {
            $service = app(MessagingService::class);
            $channelType = request('channel_type')
                ? ChannelTypeEnum::tryFrom(request('channel_type'))
                : null;

            return response()->json([
                'data' => $service->getQuickReplies(auth()->id(), $channelType),
            ]);
        })->name('quick-replies.index');
    });
});
