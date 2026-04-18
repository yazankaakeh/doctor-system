<?php

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
    public function __construct(
        protected ConversationService $conversationService,
        protected MessageService $messageService
    ) {}

    /**
     * Initialize a webchat session.
     */
    public function init(Request $request): JsonResponse
    {
        $request->validate([
            'visitor_id' => 'nullable|string|max:255',
            'visitor_name' => 'nullable|string|max:255',
            'visitor_email' => 'nullable|email|max:255',
        ]);

        // Generate or use provided visitor ID
        $visitorId = $request->input('visitor_id') ?: 'webchat_'.Str::uuid();
        $visitorName = $request->input('visitor_name', 'Website Visitor');

        // Get webchat channel
        $channel = Channel::where('type', ChannelTypeEnum::WEBCHAT)->first();

        if (! $channel || ! $channel->is_active) {
            return response()->json([
                'success' => false,
                'error' => 'WebChat is not available',
            ], 503);
        }

        // Find or create conversation
        $conversation = $this->conversationService->findOrCreate(
            $visitorId,
            ChannelTypeEnum::WEBCHAT,
            null,
            $visitorName
        );

        // Store visitor info in metadata
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
     * Send a message from the visitor.
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

        // Verify it's a webchat conversation
        if ($conversation->channel->type !== ChannelTypeEnum::WEBCHAT) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid conversation type',
            ], 400);
        }

        $channel = new WebChatChannel($conversation->channel);

        // Create inbound message DTO
        $inboundDto = $channel->receiveMessage(
            participantIdentifier: $conversation->participant_identifier,
            content: $request->input('content'),
            messageType: MessageTypeEnum::tryFrom($request->input('type', 'text')) ?? MessageTypeEnum::TEXT,
            participantName: $conversation->participant_name
        );

        // Process the message
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
     * Get messages for a conversation.
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
     * Mark messages as read.
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
     * End the webchat session.
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
