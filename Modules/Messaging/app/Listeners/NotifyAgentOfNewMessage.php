<?php

namespace Modules\Messaging\Listeners;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\AdminManagement\app\Models\Admin;
use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Events\NewMessageEvent;
use Modules\Messaging\Notifications\NewMessageNotification;

class NotifyAgentOfNewMessage implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * Agents live on the Admin model (guard = admin) because that is the model
     * that uses Spatie\HasRoles and has an `is_active` column. The plain
     * App\Models\User has neither, so any `->whereHas('roles', ...)` against
     * it throws (relation missing) and crashes the queue worker.
     *
     * To stay compatible with legacy data where `conversations.assigned_to`
     * may still be a user id, we try Admin first, then fall back to User.
     */
    public function handle(NewMessageEvent $event): void
    {
        try {
            $message = $event->message;
            $conversation = $message->conversation;

            // Only notify for inbound messages
            if ($message->direction !== MessageDirectionEnum::INBOUND) {
                return;
            }

            // Notify assigned agent
            if ($conversation->assigned_to) {
                $agent = Admin::find($conversation->assigned_to)
                    ?? User::find($conversation->assigned_to);

                if ($agent) {
                    $agent->notify(new NewMessageNotification($message));
                }

                return;
            }

            // If no agent assigned, notify all available admin agents
            $agents = Admin::query()
                ->whereHas('roles', function ($query) {
                    $query->whereIn('name', ['admin', 'messaging_agent', 'support']);
                })
                ->where('is_active', true)
                ->get();

            foreach ($agents as $agent) {
                $agent->notify(new NewMessageNotification($message));
            }
        } catch (\Throwable $e) {
            // Never let notification failures crash the queue worker or block
            // other listeners (the broadcast listener, read-receipt, etc.).
            Log::channel('messaging')->warning(
                'NotifyAgentOfNewMessage failed: '.$e->getMessage(),
                [
                    'exception' => $e::class,
                    'event_message_id' => $event->message->id ?? null,
                    'trace' => $e->getTraceAsString(),
                ]
            );
        }
    }
}
