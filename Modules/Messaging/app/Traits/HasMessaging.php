<?php

namespace Modules\Messaging\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Modules\Messaging\DataTransferObjects\MessageResultDTO;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Enums\MessageTypeEnum;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;
use Modules\Messaging\Models\Template;
use Modules\Messaging\Services\MessagingService;

/**
 * Trait to add messaging capabilities to any model.
 * Use this on models like Lead, User, Customer, etc.
 */
trait HasMessaging
{
    /**
     * Get all conversations for this model.
     */
    public function conversations(): MorphMany
    {
        return $this->morphMany(Conversation::class, 'conversable');
    }

    /**
     * Get active conversations for this model.
     */
    public function activeConversations(): MorphMany
    {
        return $this->conversations()->active();
    }

    /**
     * Get conversations by channel type.
     */
    public function getConversationsByChannel(ChannelTypeEnum $channel): Collection
    {
        return $this->conversations()
            ->whereHas('channel', fn ($q) => $q->where('type', $channel))
            ->with(['channel', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->get();
    }

    /**
     * Get or create a conversation for a specific channel.
     */
    public function getOrCreateConversation(
        ChannelTypeEnum $channel,
        ?string $participantIdentifier = null,
        ?string $participantName = null
    ): Conversation {
        // Use provided identifier or get default from model
        $identifier = $participantIdentifier ?? $this->getDefaultMessagingIdentifier();

        if (! $identifier) {
            throw new \InvalidArgumentException('No participant identifier provided or available from model.');
        }

        return app(MessagingService::class)->conversation(
            $identifier,
            $channel,
            $this,
            $participantName ?? $this->getMessagingDisplayName()
        );
    }

    /**
     * Check if model has an active conversation on a channel.
     */
    public function hasActiveConversation(ChannelTypeEnum $channel): bool
    {
        return $this->conversations()
            ->active()
            ->whereHas('channel', fn ($q) => $q->where('type', $channel))
            ->exists();
    }

    /**
     * Get the most recent conversation.
     */
    public function getLatestConversation(): ?Conversation
    {
        return $this->conversations()
            ->with(['channel', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderBy('last_message_at', 'desc')
            ->first();
    }

    /**
     * Send a message on a specific channel.
     */
    public function sendMessage(
        ChannelTypeEnum $channel,
        string $content,
        ?int $senderId = null,
        array $options = []
    ): MessageResultDTO {
        $conversation = $this->getOrCreateConversation($channel);

        return app(MessagingService::class)->send(
            $conversation,
            $content,
            $options['message_type'] ?? MessageTypeEnum::TEXT,
            $senderId,
            $options
        );
    }

    /**
     * Send a template message on a specific channel.
     */
    public function sendTemplateMessage(
        ChannelTypeEnum $channel,
        Template $template,
        array $variables = [],
        ?int $senderId = null,
        array $options = []
    ): MessageResultDTO {
        $conversation = $this->getOrCreateConversation($channel);

        return app(MessagingService::class)->sendTemplate(
            $conversation,
            $template,
            $variables,
            $senderId,
            $options
        );
    }

    /**
     * Get total unread messages count across all conversations.
     */
    public function getUnreadMessagesCount(): int
    {
        return $this->conversations()->sum('unread_count');
    }

    /**
     * Get unread count for a specific channel.
     */
    public function getUnreadCountByChannel(ChannelTypeEnum $channel): int
    {
        return $this->conversations()
            ->whereHas('channel', fn ($q) => $q->where('type', $channel))
            ->sum('unread_count');
    }

    /**
     * Mark all conversations as read.
     */
    public function markAllConversationsAsRead(): void
    {
        $this->conversations()->update(['unread_count' => 0]);

        // Also mark all inbound messages as read
        $conversationIds = $this->conversations()->pluck('id');

        Message::whereIn('conversation_id', $conversationIds)
            ->where('direction', MessageDirectionEnum::INBOUND)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get the default identifier for messaging (override in model).
     * This should return the phone number, email, or other unique identifier.
     */
    public function getDefaultMessagingIdentifier(): ?string
    {
        // Common patterns - override in your model for custom behavior
        if (method_exists($this, 'getPhoneNumber')) {
            return $this->getPhoneNumber();
        }

        if (isset($this->phone)) {
            return $this->phone;
        }

        if (isset($this->mobile)) {
            return $this->mobile;
        }

        if (isset($this->full_mobile)) {
            return $this->full_mobile;
        }

        // For related user
        if (method_exists($this, 'user') && $this->user) {
            return $this->user->full_mobile ?? $this->user->phone ?? null;
        }

        return null;
    }

    /**
     * Get the display name for messaging (override in model).
     */
    public function getMessagingDisplayName(): string
    {
        if (isset($this->name)) {
            return $this->name;
        }

        if (isset($this->full_name)) {
            return $this->full_name;
        }

        // For related user
        if (method_exists($this, 'user') && $this->user) {
            return $this->user->name ?? 'Unknown';
        }

        return 'Unknown';
    }

    /**
     * Get available channels for this model.
     * Override to customize which channels are available.
     */
    public function getAvailableMessagingChannels(): array
    {
        $channels = [];

        // Check if we have a phone number for WhatsApp
        $phone = $this->getDefaultMessagingIdentifier();
        if ($phone) {
            $channels[] = ChannelTypeEnum::WHATSAPP;
        }

        // WebChat is always available
        $channels[] = ChannelTypeEnum::WEBCHAT;

        return $channels;
    }
}
