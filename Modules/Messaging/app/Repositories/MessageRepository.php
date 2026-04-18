<?php

namespace Modules\Messaging\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Messaging\Contracts\Repositories\MessageRepositoryInterface;
use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Enums\MessageStatusEnum;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;

class MessageRepository implements MessageRepositoryInterface
{
    public function __construct(
        protected Message $model
    ) {}

    public function find(int $id, array $relations = []): ?Message
    {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    public function findByUuid(string $uuid, array $relations = []): ?Message
    {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->where('uuid', $uuid)->first();
    }

    public function findByExternalId(string $externalId, array $relations = []): ?Message
    {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->where('external_message_id', $externalId)->first();
    }

    public function getForConversation(
        Conversation|int $conversation,
        int $limit = 50,
        ?int $beforeId = null,
        array $relations = []
    ): Collection {
        $conversationId = $conversation instanceof Conversation ? $conversation->id : $conversation;

        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        $query->where('conversation_id', $conversationId);

        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getPaginatedForConversation(
        Conversation|int $conversation,
        int $perPage = 25,
        array $relations = []
    ): LengthAwarePaginator {
        $conversationId = $conversation instanceof Conversation ? $conversation->id : $conversation;

        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Message
    {
        return $this->model->create($data);
    }

    public function update(Message $message, array $data): bool
    {
        return $message->update($data);
    }

    public function delete(Message $message): bool
    {
        return $message->delete();
    }

    public function updateStatus(Message $message, MessageStatusEnum $status, array $additionalData = []): bool
    {
        $data = array_merge(['status' => $status], $additionalData);

        // Set timestamp based on status
        switch ($status) {
            case MessageStatusEnum::SENT:
                $data['sent_at'] = $data['sent_at'] ?? now();
                break;
            case MessageStatusEnum::DELIVERED:
                $data['delivered_at'] = $data['delivered_at'] ?? now();
                break;
            case MessageStatusEnum::READ:
                $data['read_at'] = $data['read_at'] ?? now();
                break;
        }

        return $message->update($data);
    }

    public function getFailedMessages(int $limit = 100): Collection
    {
        return $this->model->newQuery()
            ->where('status', MessageStatusEnum::FAILED)
            ->where('direction', MessageDirectionEnum::OUTBOUND)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }

    public function getPendingMessages(int $limit = 100): Collection
    {
        return $this->model->newQuery()
            ->where('status', MessageStatusEnum::PENDING)
            ->where('direction', MessageDirectionEnum::OUTBOUND)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }

    public function getUnreadCount(Conversation|int $conversation): int
    {
        $conversationId = $conversation instanceof Conversation ? $conversation->id : $conversation;

        return $this->model->newQuery()
            ->where('conversation_id', $conversationId)
            ->where('direction', MessageDirectionEnum::INBOUND)
            ->whereNull('read_at')
            ->count();
    }

    public function markAsRead(Conversation|int $conversation): int
    {
        $conversationId = $conversation instanceof Conversation ? $conversation->id : $conversation;

        return $this->model->newQuery()
            ->where('conversation_id', $conversationId)
            ->where('direction', MessageDirectionEnum::INBOUND)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
