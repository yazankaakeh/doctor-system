<?php

namespace Modules\Messaging\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Messaging\Enums\MessageStatusEnum;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;

interface MessageRepositoryInterface
{
    /**
     * Find message by ID.
     */
    public function find(int $id, array $relations = []): ?Message;

    /**
     * Find message by UUID.
     */
    public function findByUuid(string $uuid, array $relations = []): ?Message;

    /**
     * Find message by external ID.
     */
    public function findByExternalId(string $externalId, array $relations = []): ?Message;

    /**
     * Get messages for a conversation.
     */
    public function getForConversation(
        Conversation|int $conversation,
        int $limit = 50,
        ?int $beforeId = null,
        array $relations = []
    ): Collection;

    /**
     * Get paginated messages for a conversation.
     */
    public function getPaginatedForConversation(
        Conversation|int $conversation,
        int $perPage = 25,
        array $relations = []
    ): LengthAwarePaginator;

    /**
     * Create a new message.
     */
    public function create(array $data): Message;

    /**
     * Update a message.
     */
    public function update(Message $message, array $data): bool;

    /**
     * Delete a message.
     */
    public function delete(Message $message): bool;

    /**
     * Update message status.
     */
    public function updateStatus(Message $message, MessageStatusEnum $status, array $additionalData = []): bool;

    /**
     * Get failed messages for retry.
     */
    public function getFailedMessages(int $limit = 100): Collection;

    /**
     * Get pending messages.
     */
    public function getPendingMessages(int $limit = 100): Collection;

    /**
     * Get unread messages count for a conversation.
     */
    public function getUnreadCount(Conversation|int $conversation): int;

    /**
     * Mark messages as read.
     */
    public function markAsRead(Conversation|int $conversation): int;
}
