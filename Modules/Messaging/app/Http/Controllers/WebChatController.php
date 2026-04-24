<?php

/**
 * -----------------------------------------------------------------------------
 * WebChatController
 * -----------------------------------------------------------------------------
 *
 * Public JSON API consumed by the in-browser chat widget that sits on the
 * marketing site. Allows an anonymous visitor to:
 *
 *   - POST /webchat/init         → create/resume a conversation
 *   - POST /webchat/send         → push a message from the visitor
 *   - GET  /webchat/messages     → pull the latest N messages
 *   - POST /webchat/mark-as-read → clear the unread badge
 *   - POST /webchat/end          → close the conversation
 *
 * All persistence goes through ConversationService + MessageService so the
 * controller remains a thin HTTP-to-service adapter.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Messaging\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Messaging\Channels\WebChatChannel;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\MessageTypeEnum;
use Modules\Messaging\Models\Channel;
use Modules\Messaging\Services\ConversationService;
use Modules\Messaging\Services\MessageService;

class WebChatController extends Controller
{
    /**
     * Inject the two services that do the real work so the controller
     * is trivially swappable / testable.
     */
    public function __construct(
        protected ConversationService $conversationService,
        protected MessageService $messageService
    ) {}

    /**
     * Initialise (or resume) a webchat session. Returns the public-facing
     * Conversation UUID that the widget uses for subsequent calls, plus
     * the server-assigned visitor id so returning visitors can reconnect.
     */
    public function init(Request $request): JsonResponse
    {
        $request->validate([
            'visitor_id' => 'nullable|string|max:255',
            'visitor_name' => 'nullable|string|max:255',
            'visitor_email' => 'nullable|email|max:255',
        ]);

        // Re-use the visitor id the widget remembers; fall back to a fresh UUID.
        $visitorId   = $request->input('visitor_id') ?: 'webchat_'.Str::uuid();
        $visitorName = $request->input('visitor_name', 'Website Visitor');

        // WebChat is served from a single channel row per install.
        $channel = Channel::where('type', ChannelTypeEnum::WEBCHAT)->first();

        if (! $channel || ! $channel->is_active) {
            // 503 signals "feature is temporarily unavailable" — the widget
            // can retry later or hide itself.
            return response()->json([
                'success' => false,
                'error'   => 'WebChat is not available',
            ], 503);
        }

        // Idempotent lookup/create so reconnecting visitors keep their thread.
        $conversation = $this->conversationService->findOrCreate(
            $visitorId,
            ChannelTypeEnum::WEBCHAT,
            null,
            $visitorName
        );

        // Attach optional metadata (email address used for notifications etc.).
        if ($request->has('visitor_email')) {
            $metadata = $conversation->metadata ?? [];
            $metadata['visitor_email'] = $request->input('visitor_email');
            $conversation->update(['metadata' => $metadata]);
        }

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->uuid,
            'visitor_id' => $visitorId,
        ]);
    }

    /**
     * Store a message authored by the visitor. Wraps the raw payload in a
     * DTO via WebChatChannel::receiveMessage() so downstream services see
     * the same shape they would for any other inbound channel.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'conversation_id' => 'required|string',
            'content' => 'required|string|max:4000',
            'type' => 'nullable|string|in:text,image,document',
        ]);

        $conversation = $this->conversationService->findByUuid($request->input('conversation_id'));

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation not found',
            ], 404);
        }

        // Sanity check: the UUID must belong to a webchat conversation.
        // Otherwise the endpoint could be abused to inject messages into
        // other channels.
        if ($conversation->channel->type !== ChannelTypeEnum::WEBCHAT) {
            return response()->json([
                'success' => false,
                'error'   => 'Invalid conversation type',
            ], 400);
        }

        $channel = new WebChatChannel($conversation->channel);

        // Build the inbound DTO the same way the webhook controllers do.
        $inboundDto = $channel->receiveMessage(
            participantIdentifier: $conversation->participant_identifier,
            content: $request->input('content'),
            messageType: MessageTypeEnum::tryFrom($request->input('type', 'text')) ?? MessageTypeEnum::TEXT,
            participantName: $conversation->participant_name
        );

        // processInbound() persists the message, bumps unread count, fires
        // the NewMessageNotification, etc.
        $message = $this->messageService->processInbound($inboundDto);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->uuid,
                'content' => $message->content,
                'type' => $message->message_type->value,
                'created_at' => $message->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Paginate messages for the widget. Uses `before_id` cursor pagination
     * so the widget can lazy-load history as the visitor scrolls up.
     */
    public function getMessages(Request $request): JsonResponse
    {
        $request->validate([
            'conversation_id' => 'required|string',
            'before_id' => 'nullable|integer',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $conversation = $this->conversationService->findByUuid($request->input('conversation_id'));

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation not found',
            ], 404);
        }

        $messages = $this->messageService->getForConversation(
            $conversation,
            $request->input('limit', 50),
            $request->input('before_id')
        );

        return response()->json([
            'success' => true,
            'messages' => $messages->map(fn ($msg) => [
                'id' => $msg->id,
                'uuid' => $msg->uuid,
                'direction' => $msg->direction->value,
                'sender_type' => $msg->sender_type->value,
                'sender_name' => $msg->getSenderName(),
                'type' => $msg->message_type->value,
                'content' => $msg->content,
                'status' => $msg->status->value,
                'created_at' => $msg->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Clear the unread badge — called when the widget is visible again and
     * all outbound agent messages have been rendered.
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'conversation_id' => 'required|string',
        ]);

        $conversation = $this->conversationService->findByUuid($request->input('conversation_id'));

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation not found',
            ], 404);
        }

        $this->messageService->markAsRead($conversation);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Close the session when the visitor clicks "End chat" — transitions
     * the conversation to CLOSED so agents know it's done.
     */
    public function endSession(Request $request): JsonResponse
    {
        $request->validate([
            'conversation_id' => 'required|string',
        ]);

        $conversation = $this->conversationService->findByUuid($request->input('conversation_id'));

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation not found',
            ], 404);
        }

        $this->conversationService->close($conversation);

        return response()->json([
            'success' => true,
        ]);
    }
}
