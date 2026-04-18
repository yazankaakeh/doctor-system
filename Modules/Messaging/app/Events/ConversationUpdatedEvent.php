<?php

namespace Modules\Messaging\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Messaging\Models\Conversation;

class ConversationUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Conversation $conversation;

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation->load(['channel', 'assignedUser']);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('conversation.'.$this->conversation->id),
        ];

        // Broadcast to assigned agent
        if ($this->conversation->assigned_user_id) {
            $channels[] = new PrivateChannel('agent.'.$this->conversation->assigned_user_id);
        }

        // Broadcast to all admins
        $channels[] = new PrivateChannel('messaging.admins');

        return $channels;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'conversation' => [
                'id' => $this->conversation->id,
                'uuid' => $this->conversation->uuid,
                'channel_type' => $this->conversation->channel->type->value,
                'participant_identifier' => $this->conversation->participant_identifier,
                'participant_name' => $this->conversation->getDisplayName(),
                'assigned_user_id' => $this->conversation->assigned_user_id,
                'assigned_user_name' => $this->conversation->assignedUser?->name,
                'status' => $this->conversation->status->value,
                'priority' => $this->conversation->priority->value,
                'unread_count' => $this->conversation->unread_count,
                'last_message_at' => $this->conversation->last_message_at?->toIso8601String(),
            ],
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'conversation-updated';
    }
}
