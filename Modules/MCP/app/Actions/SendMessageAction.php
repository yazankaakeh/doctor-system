<?php

namespace Modules\MCP\Actions;

use Modules\MCP\Models\ChatMessage;
use Modules\MCP\Repository\ChatConversation\ChatConversationInterface;
use Modules\MCP\Repository\ChatMessage\ChatMessageInterface;

class SendMessageAction
{
    public function __construct(
        protected ChatMessageInterface $messageRepository,
        protected ChatConversationInterface $conversationRepository
    ) {}

    public function handle(int $conversationId, array $data): ChatMessage
    {
        // Set sender if authenticated
        if (! isset($data['sender_type']) && auth()->check()) {
            $data['sender_type'] = get_class(auth()->user());
            $data['sender_id'] = auth()->id();
        }

        // Set default values
        $data['conversation_id'] = $conversationId;
        $data['role'] = $data['role'] ?? 'user';
        $data['is_from_bot'] = $data['is_from_bot'] ?? false;

        $message = $this->messageRepository->store($data);

        // Update conversation last_message_at
        $this->conversationRepository->update($conversationId, [
            'last_message_at' => now(),
        ]);

        return $message;
    }
}
