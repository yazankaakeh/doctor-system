<?php

namespace Modules\Messaging\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Messaging\Models\Message;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Message $message
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];

        // Add email for high priority or offline agents
        if ($this->shouldSendEmail($notifiable)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $conversation = $this->message->conversation;

        return (new MailMessage)
            ->subject(__('messaging::notifications.new_message_subject', [
                'name' => $conversation->participant_name,
            ]))
            ->line(__('messaging::notifications.new_message_line', [
                'name' => $conversation->participant_name,
                'channel' => $conversation->channel->name,
            ]))
            ->line($this->truncateContent($this->message->content, 100))
            ->action(__('messaging::notifications.view_conversation'), url("/messaging/conversations/{$conversation->id}"))
            ->line(__('messaging::notifications.thanks'));
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $conversation = $this->message->conversation;

        return [
            'type' => 'new_message',
            'message_id' => $this->message->id,
            'conversation_id' => $conversation->id,
            'participant_name' => $conversation->participant_name,
            'channel_type' => $conversation->channel->type->value,
            'content_preview' => $this->truncateContent($this->message->content, 50),
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    /**
     * Determine if email should be sent.
     */
    protected function shouldSendEmail(object $notifiable): bool
    {
        // Check if user is offline (hasn't been active in last 5 minutes)
        if (isset($notifiable->last_activity_at)) {
            return $notifiable->last_activity_at < now()->subMinutes(5);
        }

        return false;
    }

    /**
     * Truncate content for preview.
     */
    protected function truncateContent(string $content, int $length): string
    {
        if (strlen($content) <= $length) {
            return $content;
        }

        return substr($content, 0, $length) . '...';
    }
}
