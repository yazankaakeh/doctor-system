<?php

namespace Modules\Messaging\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Messaging\Channels\ChannelFactory;
use Modules\Messaging\Contracts\ChannelInterface;
use Modules\Messaging\DataTransferObjects\InboundMessageDTO;
use Modules\Messaging\DataTransferObjects\MessageResultDTO;
use Modules\Messaging\DataTransferObjects\SendMessageDTO;
use Modules\Messaging\DataTransferObjects\SendTemplateDTO;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\MessageTypeEnum;
use Modules\Messaging\Models\Channel;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;
use Modules\Messaging\Models\QuickReply;
use Modules\Messaging\Models\Template;

/**
 * Main facade for the Messaging module.
 * Provides a unified API for all messaging operations.
 */
class MessagingService
{
    public function __construct(
        protected ConversationService $conversationService,
        protected MessageService $messageService
    ) {}

    // =========================================================================
    // CHANNEL OPERATIONS
    // =========================================================================

    /**
     * Get a channel instance by type.
     */
    public function channel(ChannelTypeEnum $type): ChannelInterface
    {
        return ChannelFactory::makeFromType($type);
    }

    /**
     * Get all active channels.
     */
    public function getChannels(): Collection
    {
        return Channel::active()->get();
    }

    /**
     * Get configured channels.
     */
    public function getConfiguredChannels(): array
    {
        return ChannelFactory::configured();
    }

    /**
     * Check if a channel is configured and available.
     */
    public function isChannelAvailable(ChannelTypeEnum $type): bool
    {
        try {
            $channel = $this->channel($type);

            return $channel->isConfigured();
        } catch (\Exception $e) {
            return false;
        }
    }

    // =========================================================================
    // CONVERSATION OPERATIONS
    // =========================================================================

    /**
     * Start or continue a conversation with a participant.
     */
    public function conversation(
        string $participantIdentifier,
        ChannelTypeEnum $channelType,
        ?Model $conversable = null,
        ?string $participantName = null
    ): Conversation {
        return $this->conversationService->findOrCreate(
            $participantIdentifier,
            $channelType,
            $conversable,
            $participantName
        );
    }

    /**
     * Get conversation by ID.
     */
    public function getConversation(int $id): ?Conversation
    {
        return $this->conversationService->find($id, ['channel', 'assignedUser', 'conversable']);
    }

    /**
     * Get conversation by UUID.
     */
    public function getConversationByUuid(string $uuid): ?Conversation
    {
        return $this->conversationService->findByUuid($uuid, ['channel', 'assignedUser', 'conversable']);
    }

    /**
     * Get all conversations for a model.
     */
    public function getConversationsFor(Model $model): Collection
    {
        return $this->conversationService->getForModel($model, ['channel', 'messages' => function ($q) {
            $q->latest()->limit(1);
        }]);
    }

    /**
     * Get conversations assigned to a user.
     */
    public function getAssignedConversations(User|int $user, array $filters = []): Collection
    {
        return $this->conversationService->getAssignedTo($user, $filters);
    }

    /**
     * Get unassigned conversations.
     */
    public function getUnassignedConversations(array $filters = []): Collection
    {
        return $this->conversationService->getUnassigned($filters);
    }

    /**
     * Assign conversation to a user.
     */
    public function assignConversation(Conversation $conversation, User|int $user): Conversation
    {
        return $this->conversationService->assign($conversation, $user);
    }

    /**
     * Close a conversation.
     */
    public function closeConversation(Conversation $conversation): Conversation
    {
        return $this->conversationService->close($conversation);
    }

    /**
     * Reopen a conversation.
     */
    public function reopenConversation(Conversation $conversation): Conversation
    {
        return $this->conversationService->reopen($conversation);
    }

    /**
     * Search conversations.
     */
    public function searchConversations(string $query, array $filters = []): Collection
    {
        return $this->conversationService->search($query, $filters);
    }

    /**
     * Get total unread count for a user.
     */
    public function getUnreadCount(User|int $user): int
    {
        return $this->conversationService->getTotalUnreadCount($user);
    }

    // =========================================================================
    // MESSAGE OPERATIONS
    // =========================================================================

    /**
     * Send a message to a conversation.
     */
    public function send(
        Conversation $conversation,
        string $content,
        MessageTypeEnum $messageType = MessageTypeEnum::TEXT,
        ?int $senderId = null,
        array $options = []
    ): MessageResultDTO {
        $dto = SendMessageDTO::make(
            conversation: $conversation,
            content: $content,
            messageType: $messageType,
            senderId: $senderId,
            mediaUrl: $options['media_url'] ?? null,
            mediaPath: $options['media_path'] ?? null,
            mimeType: $options['mime_type'] ?? null,
            fileName: $options['file_name'] ?? null,
            metadata: $options['metadata'] ?? []
        );

        return $this->messageService->send($dto, $options['async'] ?? true);
    }

    /**
     * Send a message to a participant (finds or creates conversation).
     */
    public function sendTo(
        string $participantIdentifier,
        ChannelTypeEnum $channelType,
        string $content,
        ?int $senderId = null,
        ?Model $conversable = null,
        array $options = []
    ): MessageResultDTO {
        $conversation = $this->conversation($participantIdentifier, $channelType, $conversable);

        return $this->send($conversation, $content, MessageTypeEnum::TEXT, $senderId, $options);
    }

    /**
     * Send a template message.
     */
    public function sendTemplate(
        Conversation $conversation,
        Template $template,
        array $variables = [],
        ?int $senderId = null,
        array $options = []
    ): MessageResultDTO {
        $dto = SendTemplateDTO::make(
            conversation: $conversation,
            template: $template,
            variables: $variables,
            senderId: $senderId,
            headerMediaUrl: $options['header_media_url'] ?? null,
            metadata: $options['metadata'] ?? []
        );

        return $this->messageService->sendTemplate($dto, $options['async'] ?? true);
    }

    /**
     * Process an inbound message.
     */
    public function receiveMessage(InboundMessageDTO $dto): Message
    {
        return $this->messageService->processInbound($dto);
    }

    /**
     * Get messages for a conversation.
     */
    public function getMessages(Conversation $conversation, int $limit = 50, ?int $beforeId = null): Collection
    {
        return $this->messageService->getForConversation($conversation, $limit, $beforeId);
    }

    /**
     * Retry a failed message.
     */
    public function retryMessage(Message $message): MessageResultDTO
    {
        return $this->messageService->retry($message);
    }

    /**
     * Mark conversation as read.
     */
    public function markAsRead(Conversation $conversation): void
    {
        $this->conversationService->markAsRead($conversation);
        $this->messageService->markAsRead($conversation);
    }

    // =========================================================================
    // NOTE OPERATIONS
    // =========================================================================

    /**
     * Add a note to a conversation.
     */
    public function addNote(Conversation $conversation, User|int $user, string $content)
    {
        return $this->conversationService->addNote($conversation, $user, $content);
    }

    /**
     * Get notes for a conversation.
     */
    public function getNotes(Conversation $conversation): Collection
    {
        return $this->conversationService->getNotes($conversation);
    }

    // =========================================================================
    // QUICK REPLY OPERATIONS
    // =========================================================================

    /**
     * Get quick replies for a user and channel.
     */
    public function getQuickReplies(User|int $user, ?ChannelTypeEnum $channelType = null): Collection
    {
        $query = QuickReply::active()
            ->forUser($user);

        if ($channelType) {
            $channelId = Channel::where('type', $channelType)->value('id');
            $query->forChannel($channelId);
        }

        return $query->orderBy('usage_count', 'desc')->get();
    }

    /**
     * Find quick reply by shortcut.
     */
    public function findQuickReply(string $shortcut, User|int $user): ?QuickReply
    {
        return QuickReply::active()
            ->forUser($user)
            ->matchingShortcut($shortcut)
            ->first();
    }
}
