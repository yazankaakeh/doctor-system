<?php

namespace Modules\MCP\Repository\ChatMessage;

use Illuminate\Database\Eloquent\Collection;
use Modules\MCP\Models\ChatMessage;

class ChatMessageRepository implements ChatMessageInterface
{
    public function store(array $data): ChatMessage
    {
        return ChatMessage::create($data);
    }

    public function find(int $id): ChatMessage
    {
        return ChatMessage::with(['conversation', 'sender', 'feedback'])
            ->findOrFail($id);
    }

    public function getByConversation(int $conversationId): Collection
    {
        return ChatMessage::where('conversation_id', $conversationId)
            ->with(['sender', 'feedback'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getLatestByConversation(int $conversationId, int $limit = 10): Collection
    {
        return ChatMessage::where('conversation_id', $conversationId)
            ->with(['sender', 'feedback'])
            ->latest()
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    public function destroy(int $id): void
    {
        $message = $this->find($id);
        $message->delete();
    }

    public function getBotMessages(int $conversationId): Collection
    {
        return ChatMessage::where('conversation_id', $conversationId)
            ->fromBot()
            ->with(['feedback'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getUserMessages(int $conversationId): Collection
    {
        return ChatMessage::where('conversation_id', $conversationId)
            ->fromUser()
            ->with(['sender'])
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
