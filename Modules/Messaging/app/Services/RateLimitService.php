<?php

namespace Modules\Messaging\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Models\Conversation;

class RateLimitService
{
    /**
     * Rate limits per channel type (messages per minute).
     */
    protected array $limits = [
        'whatsapp' => [
            'per_conversation' => 80,  // WhatsApp allows 80 messages/minute per conversation
            'per_phone' => 1000,       // Total per business phone number
            'template_per_day' => 100000, // Template messages per day
        ],
        'telegram' => [
            'per_conversation' => 30,  // Telegram allows 30 messages/second globally
            'per_bot' => 30,
        ],
        'webchat' => [
            'per_conversation' => 100,
            'per_user' => 200,
        ],
    ];

    /**
     * Check if sending a message is allowed under rate limits.
     */
    public function canSend(Conversation $conversation): bool
    {
        $channelType = $conversation->channel->type->value;
        $limits = $this->limits[$channelType] ?? ['per_conversation' => 60];

        // Check conversation-level limit
        $conversationKey = "rate_limit:conversation:{$conversation->id}";
        $conversationCount = Cache::get($conversationKey, 0);

        if ($conversationCount >= ($limits['per_conversation'] ?? 60)) {
            Log::channel('messaging')->warning('Rate limit exceeded for conversation', [
                'conversation_id' => $conversation->id,
                'count' => $conversationCount,
                'limit' => $limits['per_conversation'],
            ]);

            return false;
        }

        // Check channel-level limit for WhatsApp
        if ($channelType === 'whatsapp') {
            $phoneKey = 'rate_limit:whatsapp:phone';
            $phoneCount = Cache::get($phoneKey, 0);

            if ($phoneCount >= ($limits['per_phone'] ?? 1000)) {
                Log::channel('messaging')->warning('Rate limit exceeded for WhatsApp phone', [
                    'count' => $phoneCount,
                    'limit' => $limits['per_phone'],
                ]);

                return false;
            }
        }

        return true;
    }

    /**
     * Record a sent message for rate limiting.
     */
    public function recordSend(Conversation $conversation): void
    {
        $channelType = $conversation->channel->type->value;

        // Increment conversation counter (1 minute window)
        $conversationKey = "rate_limit:conversation:{$conversation->id}";
        Cache::increment($conversationKey);
        Cache::put($conversationKey, Cache::get($conversationKey, 1), 60);

        // Increment channel-level counter for WhatsApp
        if ($channelType === 'whatsapp') {
            $phoneKey = 'rate_limit:whatsapp:phone';
            Cache::increment($phoneKey);
            Cache::put($phoneKey, Cache::get($phoneKey, 1), 60);
        }
    }

    /**
     * Check template message rate limit (daily).
     */
    public function canSendTemplate(ChannelTypeEnum $channelType): bool
    {
        if ($channelType !== ChannelTypeEnum::WHATSAPP) {
            return true;
        }

        $key = 'rate_limit:whatsapp:template:'.date('Y-m-d');
        $count = Cache::get($key, 0);
        $limit = $this->limits['whatsapp']['template_per_day'] ?? 100000;

        return $count < $limit;
    }

    /**
     * Record a template message sent.
     */
    public function recordTemplateSend(): void
    {
        $key = 'rate_limit:whatsapp:template:'.date('Y-m-d');
        Cache::increment($key);
        // Keep for 24 hours
        Cache::put($key, Cache::get($key, 1), 86400);
    }

    /**
     * Get remaining messages allowed for a conversation.
     */
    public function getRemainingLimit(Conversation $conversation): int
    {
        $channelType = $conversation->channel->type->value;
        $limits = $this->limits[$channelType] ?? ['per_conversation' => 60];

        $conversationKey = "rate_limit:conversation:{$conversation->id}";
        $conversationCount = Cache::get($conversationKey, 0);

        return max(0, ($limits['per_conversation'] ?? 60) - $conversationCount);
    }

    /**
     * Get time until rate limit resets.
     */
    public function getResetTime(Conversation $conversation): int
    {
        $conversationKey = "rate_limit:conversation:{$conversation->id}";
        $ttl = Cache::getStore()->connection()->ttl(config('cache.prefix').$conversationKey);

        return max(0, $ttl);
    }
}
