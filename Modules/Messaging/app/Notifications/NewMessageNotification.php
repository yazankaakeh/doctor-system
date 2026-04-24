<?php

/**
 * -----------------------------------------------------------------------------
 * NewMessageNotification
 * -----------------------------------------------------------------------------
 *
 * Fired when a customer sends a new message into a conversation that an
 * internal user should be aware of. Responsible for three fan-outs:
 *
 *   - database  → stores the row for the bell dropdown + "unread badges".
 *   - broadcast → real-time push to the open agent dashboard UI via Pusher.
 *   - mail      → fallback email ONLY when the notifiable has been offline
 *                 for more than 5 minutes, so an agent looking away from
 *                 their screen still gets pinged.
 *
 * Queued so message send latency isn't impacted.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Messaging\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Messaging\Models\Message;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Message  $message  The new inbound message.
     */
    public function __construct(
        protected Message $message
    ) {}

    /**
     * Compute delivery channels. Always database + broadcast; mail is added
     * only when the recipient appears to be offline (so online agents don't
     * get duplicated notifications).
     */
    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];

        if ($this->shouldSendEmail($notifiable)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Fallback email sent to offline agents so they can pick up the thread
     * when they come back online.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $conversation = $this->message->conversation;

        return (new MailMessage)
            ->subject(__('messaging::notifications.new_message_subject', [
                'name' => $conversation->participant_name,
            ]))
            ->line(__('messaging::notifications.new_message_line', [
                'name'    => $conversation->participant_name,
                'channel' => $conversation->channel->name,
            ]))
            // Keep the preview short so email clients don't truncate awkwardly.
            ->line($this->truncateContent($this->message->content, 100))
            ->action(__('messaging::notifications.view_conversation'), url("/messaging/conversations/{$conversation->id}"))
            ->line(__('messaging::notifications.thanks'));
    }

    /**
     * Database payload — also reused by toBroadcast() so consumers only
     * deal with one shape.
     */
    public function toDatabase(object $notifiable): array
    {
        $conversation = $this->message->conversation;

        return [
            'type'             => 'new_message',
            'message_id'       => $this->message->id,
            'conversation_id'  => $conversation->id,
            'participant_name' => $conversation->participant_name,
            'channel_type'     => $conversation->channel->type->value,
            'content_preview'  => $this->truncateContent($this->message->content, 50),
            'created_at'       => $this->message->created_at->toIso8601String(),
        ];
    }

    /**
     * Real-time broadcast payload (identical to database).
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    /**
     * "Offline" heuristic: the notifiable's last_activity_at is older than
     * 5 minutes. Models that don't track activity simply never receive email
     * notifications for new messages.
     */
    protected function shouldSendEmail(object $notifiable): bool
    {
        if (isset($notifiable->last_activity_at)) {
            return $notifiable->last_activity_at < now()->subMinutes(5);
        }

        return false;
    }

    /**
     * Substring-based truncation used for email previews and the in-app
     * bell dropdown. Cheaper than Str::limit and good enough for ASCII/UTF-8
     * preview text.
     */
    protected function truncateContent(string $content, int $length): string
    {
        if (strlen($content) <= $length) {
            return $content;
        }

        return substr($content, 0, $length).'...';
    }
}
