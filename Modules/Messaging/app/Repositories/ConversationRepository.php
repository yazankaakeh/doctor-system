<?php

namespace Modules\Messaging\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Messaging\Contracts\Repositories\ConversationRepositoryInterface;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\ConversationStatusEnum;
use Modules\Messaging\Models\Channel;
use Modules\Messaging\Models\Conversation;

class ConversationRepository implements ConversationRepositoryInterface
{
    public function __construct(
        protected Conversation $model
    ) {}

    public function find(int $id, array $relations = []): ?Conversation
    {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    public function findByUuid(string $uuid, array $relations = []): ?Conversation
    {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->where('uuid', $uuid)->first();
    }

    public function findByParticipant(
        string $identifier,
        ChannelTypeEnum|Channel|int $channel,
        array $relations = []
    ): ?Conversation {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        $query->where('participant_identifier', $identifier);

        if ($channel instanceof Channel) {
            $query->where('channel_id', $channel->id);
        } elseif ($channel instanceof ChannelTypeEnum) {
            $query->whereHas('channel', fn ($q) => $q->where('type', $channel));
        } else {
            $query->where('channel_id', $channel);
        }

        return $query->latest()->first();
    }

    public function findOrCreate(
        string $identifier,
        ChannelTypeEnum|Channel|int $channel,
        array $attributes = []
    ): Conversation {
        $existing = $this->findByParticipant($identifier, $channel);

        if ($existing) {
            return $existing;
        }

        // Get channel ID
        $channelId = $channel instanceof Channel
            ? $channel->id
            : ($channel instanceof ChannelTypeEnum
                ? Channel::where('type', $channel)->first()?->id
                : $channel);

        return $this->create(array_merge([
            'channel_id' => $channelId,
            'participant_identifier' => $identifier,
        ], $attributes));
    }

    public function getForConversable(
        string $conversableType,
        int $conversableId,
        array $relations = []
    ): Collection {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query
            ->where('conversable_type', $conversableType)
            ->where('conversable_id', $conversableId)
            ->orderBy('last_message_at', 'desc')
            ->get();
    }

    public function getPaginated(
        array $filters = [],
        int $perPage = 15,
        array $relations = []
    ): LengthAwarePaginator {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        // Apply filters
        if (isset($filters['channel_id'])) {
            $query->where('channel_id', $filters['channel_id']);
        }

        if (isset($filters['channel_type'])) {
            $query->whereHas('channel', fn ($q) => $q->where('type', $filters['channel_type']));
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['assigned_user_id'])) {
            $query->where('assigned_user_id', $filters['assigned_user_id']);
        }

        if (isset($filters['unassigned']) && $filters['unassigned']) {
            $query->whereNull('assigned_user_id');
        }

        if (isset($filters['has_unread']) && $filters['has_unread']) {
            $query->where('unread_count', '>', 0);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('participant_name', 'like', "%{$search}%")
                    ->orWhere('participant_identifier', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderBy('last_message_at', 'desc')
            ->paginate($perPage);
    }

    public function getAssignedTo(int $userId, array $filters = [], array $relations = []): Collection
    {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        $query->where('assigned_user_id', $userId);

        // Apply additional filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['channel_type'])) {
            $query->whereHas('channel', fn ($q) => $q->where('type', $filters['channel_type']));
        }

        return $query->orderBy('last_message_at', 'desc')->get();
    }

    public function getUnassigned(array $filters = [], array $relations = []): Collection
    {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        $query->whereNull('assigned_user_id')->active();

        if (isset($filters['channel_type'])) {
            $query->whereHas('channel', fn ($q) => $q->where('type', $filters['channel_type']));
        }

        return $query->orderBy('last_message_at', 'desc')->get();
    }

    public function getByStatus(ConversationStatusEnum $status, array $relations = []): Collection
    {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query
            ->where('status', $status)
            ->orderBy('last_message_at', 'desc')
            ->get();
    }

    public function create(array $data): Conversation
    {
        return $this->model->create($data);
    }

    public function update(Conversation $conversation, array $data): bool
    {
        return $conversation->update($data);
    }

    public function delete(Conversation $conversation): bool
    {
        return $conversation->delete();
    }

    public function getTotalUnreadCount(int $userId): int
    {
        $user = User::find($userId);

        if (! $user) {
            return 0;
        }

        // For admins: show all unread (assigned to them OR unassigned)
        // For regular users: show only conversations assigned to them or where they are participant
        if ($user->isAdmin()) {
            return $this->model->newQuery()
                ->where('unread_count', '>', 0)
                ->where(function ($q) use ($user) {
                    // Conversations assigned to this admin
                    $q->where('assigned_user_id', $user->id)
                        // OR unassigned conversations (need attention)
                        ->orWhereNull('assigned_user_id');
                })
                ->sum('unread_count');
        }

        // For regular agents/users
        return $this->model->newQuery()
            ->where('unread_count', '>', 0)
            ->where(function ($q) use ($user) {
                // Conversations assigned to user (as agent)
                $q->where('assigned_user_id', $user->id);

                // OR conversations where user is the participant (for webchat)
                if ($user->full_mobile) {
                    $q->orWhere('participant_identifier', $user->full_mobile);
                }
                if ($user->email) {
                    $q->orWhere('participant_identifier', $user->email);
                }
                // Also check for user:ID format
                $q->orWhere('participant_identifier', "user:{$user->id}");
            })
            ->sum('unread_count');
    }

    public function search(string $query, array $filters = [], int $limit = 20): Collection
    {
        $builder = $this->model->newQuery()
            ->with(['channel', 'assignedUser']);

        // Search in participant info and messages
        $builder->where(function ($q) use ($query) {
            $q->where('participant_name', 'like', "%{$query}%")
                ->orWhere('participant_identifier', 'like', "%{$query}%")
                ->orWhereHas('messages', fn ($mq) => $mq->where('content', 'like', "%{$query}%"));
        });

        // Apply filters
        if (isset($filters['channel_type'])) {
            $builder->whereHas('channel', fn ($q) => $q->where('type', $filters['channel_type']));
        }

        if (isset($filters['assigned_user_id'])) {
            $builder->where('assigned_user_id', $filters['assigned_user_id']);
        }

        return $builder
            ->orderBy('last_message_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
