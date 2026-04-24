<?php

/**
 * -----------------------------------------------------------------------------
 * ConversationAssignedNotification
 * -----------------------------------------------------------------------------
 *
 * Fired when a conversation gets assigned to an internal user (e.g. a
 * supervisor re-assigns a ticket to an agent). Delivered over:
 *
 *   - database  → persists the entry for the bell dropdown.
 *   - broadcast → pushes a real-time Pusher/Reverb event so open agent
 *                  dashboards can flash the notification instantly.
 *
 * Queued so the assignment UI stays snappy.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Messaging\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Modules\Messaging\Models\Conversation;

class ConversationAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Conversation  $conversation  The thread that was just assigned.
     */
    public function __construct(
        protected Conversation $conversation
    ) {}

    /**
     * Delivery channels. No email/push — the dashboard is the source of truth
     * for assignment events.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Payload persisted in the notifications table. Shape is stable — the
     * dashboard component reads these keys directly.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'conversation_assigned',
            'conversation_id' => $this->conversation->id,
            'participant_name' => $this->conversation->participant_name,
            'channel_type' => $this->conversation->channel->type->value,
            'unread_count' => $this->conversation->unread_count,
            'assigned_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Real-time broadcast payload. We intentionally reuse the database
     * payload so consumers only need one schema.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}
