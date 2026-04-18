<?php

namespace Modules\Messaging\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\ConversationStatusEnum;
use Modules\Messaging\Models\Channel;
use Modules\Messaging\Models\Conversation;

interface ConversationRepositoryInterface
{
    /**
     * Find conversation by ID.
     */
    public function find(int $id, array $relations = []): ?Conversation;

    /**
     * Find conversation by UUID.
     */
    public function findByUuid(string $uuid, array $relations = []): ?Conversation;

    /**
     * Find conversation by participant identifier and channel.
     */
    public function findByParticipant(
        string $identifier,
        ChannelTypeEnum|Channel|int $channel,
        array $relations = []
    ): ?Conversation;

    /**
     * Find or create conversation for a participant.
     */
    public function findOrCreate(
        string $identifier,
        ChannelTypeEnum|Channel|int $channel,
        array $attributes = []
    ): Conversation;

    /**
     * Get conversations for a conversable model.
     */
    public function getForConversable(
        string $conversableType,
        int $conversableId,
        array $relations = []
    ): Collection;

    /**
     * Get paginated conversations with filters.
     */
    public function getPaginated(
        array $filters = [],
        int $perPage = 15,
        array $relations = []
    ): LengthAwarePaginator;

    /**
     * Get conversations assigned to a user.
     */
    public function getAssignedTo(int $userId, array $filters = [], array $relations = []): Collection;

    /**
     * Get unassigned conversations.
     */
    public function getUnassigned(array $filters = [], array $relations = []): Collection;

    /**
     * Get conversations by status.
     */
    public function getByStatus(ConversationStatusEnum $status, array $relations = []): Collection;

    /**
     * Create a new conversation.
     */
    public function create(array $data): Conversation;

    /**
     * Update a conversation.
     */
    public function update(Conversation $conversation, array $data): bool;

    /**
     * Delete a conversation.
     */
    public function delete(Conversation $conversation): bool;

    /**
     * Get total unread count for a user.
     */
    public function getTotalUnreadCount(int $userId): int;

    /**
     * Search conversations.
     */
    public function search(string $query, array $filters = [], int $limit = 20): Collection;
}
