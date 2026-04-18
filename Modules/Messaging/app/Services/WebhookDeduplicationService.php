<?php

namespace Modules\Messaging\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WebhookDeduplicationService
{
    /**
     * Default TTL for deduplication keys (in seconds).
     */
    protected int $ttl = 3600; // 1 hour

    /**
     * Check if a webhook has already been processed.
     */
    public function isDuplicate(string $webhookId, string $channel = 'whatsapp'): bool
    {
        $key = $this->getCacheKey($webhookId, $channel);

        if (Cache::has($key)) {
            Log::channel('messaging')->info('Duplicate webhook detected', [
                'webhook_id' => $webhookId,
                'channel' => $channel,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Mark a webhook as processed.
     */
    public function markAsProcessed(string $webhookId, string $channel = 'whatsapp'): void
    {
        $key = $this->getCacheKey($webhookId, $channel);
        Cache::put($key, true, $this->ttl);
    }

    /**
     * Check and mark in one atomic operation.
     */
    public function checkAndMark(string $webhookId, string $channel = 'whatsapp'): bool
    {
        $key = $this->getCacheKey($webhookId, $channel);

        // Use atomic add - returns false if key already exists
        $added = Cache::add($key, true, $this->ttl);

        if (! $added) {
            Log::channel('messaging')->info('Duplicate webhook detected (atomic)', [
                'webhook_id' => $webhookId,
                'channel' => $channel,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Generate a unique webhook ID from payload.
     */
    public function generateWebhookId(array $payload, string $channel = 'whatsapp'): string
    {
        // Try to extract a unique identifier from the payload
        $uniqueId = match ($channel) {
            'whatsapp' => $this->extractWhatsAppId($payload),
            'telegram' => $this->extractTelegramId($payload),
            default => md5(json_encode($payload)),
        };

        return $uniqueId;
    }

    /**
     * Extract unique ID from WhatsApp webhook payload.
     */
    protected function extractWhatsAppId(array $payload): string
    {
        // WhatsApp provides unique message IDs
        $entry = $payload['entry'][0] ?? [];
        $changes = $entry['changes'][0] ?? [];
        $value = $changes['value'] ?? [];

        // For messages
        if (isset($value['messages'][0]['id'])) {
            return 'msg_'.$value['messages'][0]['id'];
        }

        // For status updates
        if (isset($value['statuses'][0]['id'])) {
            return 'status_'.$value['statuses'][0]['id'].'_'.($value['statuses'][0]['status'] ?? '');
        }

        // Fallback to hash
        return md5(json_encode($payload));
    }

    /**
     * Extract unique ID from Telegram webhook payload.
     */
    protected function extractTelegramId(array $payload): string
    {
        // Telegram provides update_id
        if (isset($payload['update_id'])) {
            return 'update_'.$payload['update_id'];
        }

        // Message ID
        if (isset($payload['message']['message_id'])) {
            return 'msg_'.$payload['message']['message_id'];
        }

        // Fallback to hash
        return md5(json_encode($payload));
    }

    /**
     * Get the cache key for a webhook.
     */
    protected function getCacheKey(string $webhookId, string $channel): string
    {
        return "webhook:dedup:{$channel}:{$webhookId}";
    }

    /**
     * Clear old deduplication entries (for maintenance).
     */
    public function cleanup(): int
    {
        // This is handled automatically by cache TTL
        // This method can be used for database-based deduplication
        return 0;
    }
}
