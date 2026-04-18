<?php

namespace Modules\Messaging\Listeners;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Events\NewMessageEvent;
use Modules\Messaging\Notifications\NewMessageNotification;

class NotifyAgentOfNewMessage implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(NewMessageEvent $event): void
    {
        $message = $event->message;
        $conversation = $message->conversation;

        // Only notify for inbound messages
        if ($message->direction !== MessageDirectionEnum::INBOUND) {
            return;
        }

        // Notify assigned agent
        if ($conversation->assigned_to) {
            $agent = User::find($conversation->assigned_to);
            if ($agent) {
                $agent->notify(new NewMessageNotification($message));
            }

            return;
        }

        // If no agent assigned, notify all available agents
        $agents = User::query()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'messaging_agent', 'support']);
            })
            ->where('is_active', true)
            ->get();

        foreach ($agents as $agent) {
            $agent->notify(new NewMessageNotification($message));
        }
    }
}
