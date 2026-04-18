<?php

namespace Modules\MCP\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\MCP\Actions\CreateConversationAction;
use Modules\MCP\Actions\ProcessChatMessageAction;
use Modules\MCP\Actions\StoreFeedbackAction;
use Modules\MCP\Http\Requests\CreateConversationRequest;
use Modules\MCP\Http\Requests\SendMessageRequest;
use Modules\MCP\Http\Requests\StoreFeedbackRequest;
use Modules\MCP\Repository\ChatConversation\ChatConversationInterface;
use Modules\MCP\Repository\ChatMessage\ChatMessageInterface;

class ChatBotController extends Controller
{
    public function __construct(
        protected ChatConversationInterface $conversationRepository,
        protected ChatMessageInterface $messageRepository,
        protected CreateConversationAction $createConversationAction,
        protected ProcessChatMessageAction $processChatMessageAction,
        protected StoreFeedbackAction $storeFeedbackAction
    ) {}

    /**
     * Start a new conversation
     */
    public function startConversation(CreateConversationRequest $request): JsonResponse
    {
        $conversation = $this->createConversationAction->handle($request->validated());

        return response()->json([
            'success' => true,
            'data' => [
                'conversation' => $conversation,
                'greeting' => config('mcp.system_prompts.greeting'),
            ],
        ], 201);
    }

    /**
     * Send a message and get AI response
     */
    public function sendMessage(SendMessageRequest $request): JsonResponse
    {
        $result = $this->processChatMessageAction->handle(
            $request->input('conversation_id'),
            $request->input('message')
        );

        return response()->json([
            'success' => true,
            'data' => [
                'user_message' => $result['user_message'],
                'bot_message' => $result['bot_message'],
            ],
        ]);
    }

    /**
     * Get conversation history
     */
    public function getConversation(int $conversationId): JsonResponse
    {
        $conversation = $this->conversationRepository->find($conversationId);
        $messages = $this->messageRepository->getByConversation($conversationId);

        return response()->json([
            'success' => true,
            'data' => [
                'conversation' => $conversation,
                'messages' => $messages,
            ],
        ]);
    }

    /**
     * Get conversation by session ID
     */
    public function getBySession(Request $request): JsonResponse
    {
        $sessionId = $request->input('session_id');
        $conversation = $this->conversationRepository->findBySessionId($sessionId);

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }

        $messages = $this->messageRepository->getByConversation($conversation->id);

        return response()->json([
            'success' => true,
            'data' => [
                'conversation' => $conversation,
                'messages' => $messages,
            ],
        ]);
    }

    /**
     * Close conversation
     */
    public function closeConversation(int $conversationId): JsonResponse
    {
        $this->conversationRepository->close($conversationId);

        return response()->json([
            'success' => true,
            'message' => 'Conversation closed successfully',
        ]);
    }

    /**
     * Submit feedback
     */
    public function submitFeedback(StoreFeedbackRequest $request): JsonResponse
    {
        $feedback = $this->storeFeedbackAction->handle($request->validated());

        return response()->json([
            'success' => true,
            'data' => $feedback,
            'message' => 'Thank you for your feedback!',
        ], 201);
    }

    /**
     * Get active conversations (for admin)
     */
    public function getActiveConversations(): JsonResponse
    {
        $conversations = $this->conversationRepository->getActiveConversations();

        return response()->json([
            'success' => true,
            'data' => $conversations,
        ]);
    }
}
