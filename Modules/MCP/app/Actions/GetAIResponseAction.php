<?php

namespace Modules\MCP\Actions;

use Modules\MCP\Models\ChatMessage;
use Modules\MCP\Repository\ChatConversation\ChatConversationInterface;
use Modules\MCP\Repository\ChatMessage\ChatMessageInterface;
use Modules\MCP\Services\AI\AIServiceFactory;

class GetAIResponseAction
{
    public function __construct(
        protected ChatMessageInterface $messageRepository,
        protected ChatConversationInterface $conversationRepository,
        protected SearchKnowledgeBaseAction $searchKnowledgeAction
    ) {}

    public function handle(int $conversationId, string $userMessage, ?string $provider = null): ChatMessage
    {
        // Get conversation history
        $conversationMessages = $this->messageRepository
            ->getLatestByConversation($conversationId)
            ->toArray();

        // Search knowledge base for relevant information
        $knowledge = $this->searchKnowledgeAction->handle($userMessage);

        // Build context
        $context = [
            'messages' => $conversationMessages,
            'knowledge' => $knowledge,
        ];

        // Get AI response
        $aiService = AIServiceFactory::make($provider);
        $response = $aiService->generateResponse($userMessage, $context);

        // Store AI message
        $messageData = [
            'conversation_id' => $conversationId,
            'sender_type' => 'App\Models\Bot', // Generic bot type
            'sender_id' => 1, // Bot ID
            'role' => 'assistant',
            'message' => $response['message'],
            'is_from_bot' => true,
            'model_used' => $response['model'],
            'tokens_used' => $response['tokens_used'],
            'context' => [
                'knowledge_used' => ! empty($knowledge),
                'knowledge_count' => count($knowledge),
            ],
            'metadata' => $response['metadata'],
        ];

        $message = $this->messageRepository->store($messageData);

        // Update conversation last_message_at
        $this->conversationRepository->update($conversationId, [
            'last_message_at' => now(),
        ]);

        return $message;
    }
}
