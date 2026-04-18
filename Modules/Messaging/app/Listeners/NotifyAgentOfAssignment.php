<?php

namespace Modules\Messaging\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Messaging\Events\ConversationAssigned;
use Modules\Messaging\Notifications\ConversationAssignedNotification;

class NotifyAgentOfAssignment implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ConversationAssigned $event): void
    {
        $agent = $event->assignedTo;
        $conversation = $event->conversation;

        // Notify the new assigned agent
        $agent->notify(new ConversationAssignedNotification($conversation));
    }
}
