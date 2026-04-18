<?php

namespace Modules\Messaging\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Messaging\Models\Conversation;

class ConversationAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public User $assignedTo,
        public ?int $previousAgentId = null
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('messaging.agent.' . $this->assignedTo->id),
        ];

        // Also notify the previous agent if there was one
        if ($this->previousAgentId) {
            $channels[] = new PrivateChannel('messaging.agent.' . $this->previousAgentId);
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'assigned_to' => $this->assignedTo->id,
            'assigned_to_name' => $this->assignedTo->name,
            'previous_agent_id' => $this->previousAgentId,
            'participant_name' => $this->conversation->participant_name,
            'channel_type' => $this->conversation->channel->type->value,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'conversation.assigned';
    }
}
