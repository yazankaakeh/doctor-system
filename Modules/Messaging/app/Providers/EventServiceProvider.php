<?php

namespace Modules\Messaging\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Messaging\Events\AgentTyping;
use Modules\Messaging\Events\ConversationAssigned;
use Modules\Messaging\Events\MessageStatusUpdated;
use Modules\Messaging\Events\NewMessageEvent;
use Modules\Messaging\Listeners\NotifyAgentOfAssignment;
use Modules\Messaging\Listeners\NotifyAgentOfNewMessage;
use Modules\Messaging\Listeners\SendWhatsAppReadReceipt;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        NewMessageEvent::class => [
            NotifyAgentOfNewMessage::class,
            SendWhatsAppReadReceipt::class,
        ],
        ConversationAssigned::class => [
            NotifyAgentOfAssignment::class,
        ],
        MessageStatusUpdated::class => [
            // Status updates are broadcast automatically by the event
        ],
        AgentTyping::class => [
            // Typing indicators are broadcast automatically by the event
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
