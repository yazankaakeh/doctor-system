<?php

namespace Modules\Messaging\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Booking\Models\Booking;
use Modules\Messaging\Models\Message;

class NewMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load(['conversation.channel', 'sender']);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('conversation.'.$this->message->conversation_id),
        ];

        $conversation = $this->message->conversation;

        // Check if this is a booking conversation (doctor-patient chat)
        if ($conversation->conversable_type === Booking::class) {
            $metadata = $conversation->metadata ?? [];

            // Broadcast to doctor channel
            if (isset($metadata['doctor_id'])) {
                $channels[] = new PrivateChannel('doctor.'.$metadata['doctor_id']);
            }

            // Broadcast to patient channel
            if (isset($metadata['patient_id'])) {
                $channels[] = new PrivateChannel('patient.'.$metadata['patient_id']);
            }

            return $channels;
        }

        // Also broadcast to the assigned agent's channel
        if ($conversation->assigned_user_id) {
            $channels[] = new PrivateChannel('agent.'.$conversation->assigned_user_id);
        }

        // Broadcast to all admins for unassigned conversations
        if (! $conversation->assigned_user_id) {
            $channels[] = new PrivateChannel('messaging.unassigned');
        }

        // Broadcast to participant if they're a user in the system
        $participantIdentifier = $conversation->participant_identifier;
        if ($participantIdentifier) {
            $participantUser = User::where('email', $participantIdentifier)
                ->orWhereHas('userInfo', fn ($q) => $q->whereRaw('CONCAT(mobile_intro, mobile) = ?', [$participantIdentifier]))
                ->first();

            if ($participantUser && $participantUser->id !== $this->message->sender_id) {
                $channels[] = new PrivateChannel('agent.'.$participantUser->id);
            }
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'uuid' => $this->message->uuid,
                'conversation_id' => $this->message->conversation_id,
                'sender_type' => $this->message->sender_type->value,
                'sender_id' => $this->message->sender_id,
                'sender_name' => $this->message->getSenderName(),
                'direction' => $this->message->direction->value,
                'message_type' => $this->message->message_type->value,
                'content' => $this->message->content,
                'media_url' => $this->message->media_url,
                'status' => $this->message->status->value,
                'created_at' => $this->message->created_at->toIso8601String(),
            ],
            'conversation' => [
                'id' => $this->message->conversation->id,
                'uuid' => $this->message->conversation->uuid,
                'channel_type' => $this->message->conversation->channel->type->value,
                'participant_name' => $this->message->conversation->getDisplayName(),
                'unread_count' => $this->message->conversation->unread_count,
            ],
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'new-message';
    }
}
