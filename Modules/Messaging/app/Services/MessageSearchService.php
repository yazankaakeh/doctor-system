<?php

namespace Modules\Messaging\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Enums\MessageStatusEnum;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;

class MessageSearchService
{
    /**
     * Search messages with filters.
     */
    public function search(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Message::query()
            ->with(['conversation', 'conversation.channel', 'sender']);

        // Text search
        if (! empty($filters['query'])) {
            $searchTerm = $filters['query'];
            $query->where('content', 'LIKE', "%{$searchTerm}%");
        }

        // Channel filter
        if (! empty($filters['channel'])) {
            $channelType = $filters['channel'] instanceof ChannelTypeEnum
                ? $filters['channel']
                : ChannelTypeEnum::tryFrom($filters['channel']);

            if ($channelType) {
                $query->whereHas('conversation.channel', function ($q) use ($channelType) {
                    $q->where('type', $channelType);
                });
            }
        }

        // Conversation filter
        if (! empty($filters['conversation_id'])) {
            $query->where('conversation_id', $filters['conversation_id']);
        }

        // Direction filter
        if (! empty($filters['direction'])) {
            $direction = $filters['direction'] instanceof MessageDirectionEnum
                ? $filters['direction']
                : MessageDirectionEnum::tryFrom($filters['direction']);

            if ($direction) {
                $query->where('direction', $direction);
            }
        }

        // Status filter
        if (! empty($filters['status'])) {
            $status = $filters['status'] instanceof MessageStatusEnum
                ? $filters['status']
                : MessageStatusEnum::tryFrom($filters['status']);

            if ($status) {
                $query->where('status', $status);
            }
        }

        // Date range
        if (! empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        // Sender filter
        if (! empty($filters['sender_id'])) {
            $query->where('sender_id', $filters['sender_id']);
        }

        // Has media filter
        if (isset($filters['has_media'])) {
            if ($filters['has_media']) {
                $query->whereNotNull('media_url');
            } else {
                $query->whereNull('media_url');
            }
        }

        // Participant filter
        if (! empty($filters['participant'])) {
            $query->whereHas('conversation', function ($q) use ($filters) {
                $q->where('participant_identifier', 'LIKE', "%{$filters['participant']}%")
                    ->orWhere('participant_name', 'LIKE', "%{$filters['participant']}%");
            });
        }

        // Order
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Search within a specific conversation.
     */
    public function searchInConversation(Conversation $conversation, string $query): Collection
    {
        return Message::where('conversation_id', $conversation->id)
            ->where('content', 'LIKE', "%{$query}%")
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
    }

    /**
     * Get message statistics.
     */
    public function getStatistics(array $filters = []): array
    {
        $query = Message::query();

        // Apply date filters
        if (! empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return [
            'total_messages' => (clone $query)->count(),
            'inbound_messages' => (clone $query)->where('direction', MessageDirectionEnum::INBOUND)->count(),
            'outbound_messages' => (clone $query)->where('direction', MessageDirectionEnum::OUTBOUND)->count(),
            'delivered_messages' => (clone $query)->where('status', MessageStatusEnum::DELIVERED)->count(),
            'read_messages' => (clone $query)->where('status', MessageStatusEnum::READ)->count(),
            'failed_messages' => (clone $query)->where('status', MessageStatusEnum::FAILED)->count(),
            'by_channel' => $this->getMessagesByChannel($query),
            'by_day' => $this->getMessagesByDay($query),
        ];
    }

    /**
     * Get messages grouped by channel.
     */
    protected function getMessagesByChannel(Builder $query): array
    {
        return (clone $query)
            ->selectRaw('COUNT(*) as count')
            ->join('messaging_conversations', 'messaging_messages.conversation_id', '=', 'messaging_conversations.id')
            ->join('messaging_channels', 'messaging_conversations.channel_id', '=', 'messaging_channels.id')
            ->groupBy('messaging_channels.type')
            ->pluck('count', 'messaging_channels.type')
            ->toArray();
    }

    /**
     * Get messages grouped by day.
     */
    protected function getMessagesByDay(Builder $query): array
    {
        return (clone $query)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->pluck('count', 'date')
            ->toArray();
    }

    /**
     * Full-text search (for databases that support it).
     */
    public function fullTextSearch(string $query, int $limit = 50): Collection
    {
        // For MySQL with full-text index
        // Adjust based on your database setup
        return Message::whereRaw('MATCH(content) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
            ->with(['conversation', 'conversation.channel'])
            ->limit($limit)
            ->get();
    }

    /**
     * Export messages to array for CSV/Excel export.
     */
    public function exportMessages(array $filters = []): array
    {
        $messages = $this->search($filters, 10000);

        return $messages->map(function ($message) {
            return [
                'ID' => $message->id,
                'Conversation' => $message->conversation->participant_name,
                'Channel' => $message->conversation->channel->type->value,
                'Direction' => $message->direction->value,
                'Content' => $message->content,
                'Status' => $message->status->value,
                'Sender' => $message->sender?->name ?? 'System',
                'Created At' => $message->created_at->toDateTimeString(),
                'Read At' => $message->read_at?->toDateTimeString(),
            ];
        })->toArray();
    }
}
