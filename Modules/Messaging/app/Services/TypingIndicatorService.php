<?php

namespace Modules\Messaging\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Modules\Messaging\Events\AgentTyping;
use Modules\Messaging\Models\Conversation;

class TypingIndicatorService
{
    /**
     * Typing indicator TTL in seconds.
     */
    protected int $ttl = 5;

    /**
     * Broadcast that an agent is typing.
     */
    public function setTyping(Conversation $conversation, User $agent): void
    {
        $key = $this->getCacheKey($conversation->id, $agent->id);
        Cache::put($key, true, $this->ttl);

        event(new AgentTyping($conversation, $agent, true));
    }

    /**
     * Broadcast that an agent stopped typing.
     */
    public function clearTyping(Conversation $conversation, User $agent): void
    {
        $key = $this->getCacheKey($conversation->id, $agent->id);
        Cache::forget($key);

        event(new AgentTyping($conversation, $agent, false));
    }

    /**
     * Check if an agent is typing.
     */
    public function isTyping(Conversation $conversation, User $agent): bool
    {
        $key = $this->getCacheKey($conversation->id, $agent->id);

        return Cache::has($key);
    }

    /**
     * Get all agents currently typing in a conversation.
     */
    public function getTypingAgents(Conversation $conversation): array
    {
        $typingAgents = [];

        // Check assigned agent
        if ($conversation->assigned_to) {
            $key = $this->getCacheKey($conversation->id, $conversation->assigned_to);
            if (Cache::has($key)) {
                $typingAgents[] = $conversation->assigned_to;
            }
        }

        return $typingAgents;
    }

    /**
     * Generate cache key.
     */
    protected function getCacheKey(int $conversationId, int $agentId): string
    {
        return "typing:conversation:{$conversationId}:agent:{$agentId}";
    }
}
