<?php

namespace Modules\Messaging\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Messaging\Contracts\Repositories\ConversationRepositoryInterface;
use Modules\Messaging\DataTransferObjects\CreateConversationDTO;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\ConversationPriorityEnum;
use Modules\Messaging\Enums\ConversationStatusEnum;
use Modules\Messaging\Events\ConversationUpdatedEvent;
use Modules\Messaging\Models\Channel;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\ConversationNote;

class ConversationService
{
    public function __construct(
        protected ConversationRepositoryInterface $repository
    ) {}

    /**
     * Find or create a conversation for a participant.
     */
    public function findOrCreate(
        string $participantIdentifier,
        ChannelTypeEnum|Channel|int $channel,
        ?Model $conversable = null,
        ?string $participantName = null
    ): Conversation {
        // Find existing active conversation
        $conversation = $this->repository->findByParticipant($participantIdentifier, $channel);

        if ($conversation && $conversation->isActive()) {
            // Update participant name if provided and different
            if ($participantName && $conversation->participant_name !== $participantName) {
                $conversation->update(['participant_name' => $participantName]);
            }

            // Link to conversable if not already linked
            if ($conversable && ! $conversation->conversable_id) {
                $conversation->update([
                    'conversable_type' => get_class($conversable),
                    'conversable_id' => $conversable->getKey(),
                ]);
            }

            return $conversation;
        }

        // Create new conversation
        return $this->create(CreateConversationDTO::make(
            channel: $channel,
            participantIdentifier: $participantIdentifier,
            participantName: $participantName,
            conversable: $conversable
        ));
    }

    /**
     * Create a new conversation.
     */
    public function create(CreateConversationDTO $dto): Conversation
    {
        return $this->repository->create($dto->toArray());
    }

    /**
     * Get conversation by ID.
     */
    public function find(int $id, array $relations = []): ?Conversation
    {
        return $this->repository->find($id, $relations);
    }

    /**
     * Get conversation by UUID.
     */
    public function findByUuid(string $uuid, array $relations = []): ?Conversation
    {
        return $this->repository->findByUuid($uuid, $relations);
    }

    /**
     * Get all conversations for a conversable model.
     */
    public function getForModel(Model $model, array $relations = []): Collection
    {
        return $this->repository->getForConversable(
            get_class($model),
            $model->getKey(),
            $relations
        );
    }

    /**
     * Get conversations assigned to a user.
     */
    public function getAssignedTo(User|int $user, array $filters = []): Collection
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->repository->getAssignedTo($userId, $filters, ['channel', 'messages' => function ($q) {
            $q->latest()->limit(1);
        }]);
    }

    /**
     * Get unassigned conversations.
     */
    public function getUnassigned(array $filters = []): Collection
    {
        return $this->repository->getUnassigned($filters, ['channel', 'messages' => function ($q) {
            $q->latest()->limit(1);
        }]);
    }

    /**
     * Assign conversation to a user.
     */
    public function assign(Conversation $conversation, User|int $user): Conversation
    {
        $userId = $user instanceof User ? $user->id : $user;

        $conversation->update(['assigned_user_id' => $userId]);

        event(new ConversationUpdatedEvent($conversation->fresh()));

        return $conversation->fresh();
    }

    /**
     * Unassign conversation.
     */
    public function unassign(Conversation $conversation): Conversation
    {
        $conversation->update(['assigned_user_id' => null]);

        event(new ConversationUpdatedEvent($conversation->fresh()));

        return $conversation->fresh();
    }

    /**
     * Update conversation status.
     */
    public function updateStatus(Conversation $conversation, ConversationStatusEnum $status): Conversation
    {
        $conversation->update(['status' => $status]);

        event(new ConversationUpdatedEvent($conversation->fresh()));

        return $conversation->fresh();
    }

    /**
     * Update conversation priority.
     */
    public function updatePriority(Conversation $conversation, ConversationPriorityEnum $priority): Conversation
    {
        $conversation->update(['priority' => $priority]);

        return $conversation->fresh();
    }

    /**
     * Close a conversation.
     */
    public function close(Conversation $conversation): Conversation
    {
        return $this->updateStatus($conversation, ConversationStatusEnum::CLOSED);
    }

    /**
     * Reopen a conversation.
     */
    public function reopen(Conversation $conversation): Conversation
    {
        return $this->updateStatus($conversation, ConversationStatusEnum::OPEN);
    }

    /**
     * Mark conversation as read.
     */
    public function markAsRead(Conversation $conversation): Conversation
    {
        $conversation->markAsRead();

        return $conversation->fresh();
    }

    /**
     * Get total unread count for a user.
     */
    public function getTotalUnreadCount(User|int $user): int
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->repository->getTotalUnreadCount($userId);
    }

    /**
     * Search conversations.
     */
    public function search(string $query, array $filters = [], int $limit = 20): Collection
    {
        return $this->repository->search($query, $filters, $limit);
    }

    /**
     * Add a note to a conversation.
     */
    public function addNote(Conversation $conversation, User|int $user, string $content): ConversationNote
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $conversation->notes()->create([
            'user_id' => $userId,
            'content' => $content,
        ]);
    }

    /**
     * Get notes for a conversation.
     */
    public function getNotes(Conversation $conversation): Collection
    {
        return $conversation->notes()
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Link a conversable model to a conversation.
     */
    public function linkToModel(Conversation $conversation, Model $model): Conversation
    {
        $conversation->update([
            'conversable_type' => get_class($model),
            'conversable_id' => $model->getKey(),
        ]);

        return $conversation->fresh();
    }
}
