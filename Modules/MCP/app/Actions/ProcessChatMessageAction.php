<?php

namespace Modules\MCP\Actions;

class ProcessChatMessageAction
{
    public function __construct(
        protected SendMessageAction $sendMessageAction,
        protected GetAIResponseAction $getAIResponseAction
    ) {}

    public function handle(int $conversationId, string $userMessage): array
    {
        // Store user message
        $userMessageRecord = $this->sendMessageAction->handle($conversationId, [
            'message' => $userMessage,
            'role' => 'user',
            'is_from_bot' => false,
        ]);

        // Get AI response
        $botMessageRecord = $this->getAIResponseAction->handle(
            $conversationId,
            $userMessage
        );

        return [
            'user_message' => $userMessageRecord,
            'bot_message' => $botMessageRecord,
        ];
    }
}
