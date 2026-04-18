<?php

namespace Modules\Messaging\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Events\NewMessageEvent;
use Modules\Messaging\Services\WhatsAppReadReceiptService;

class SendWhatsAppReadReceipt implements ShouldQueue
{
    public function __construct(
        protected WhatsAppReadReceiptService $readReceiptService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(NewMessageEvent $event): void
    {
        $message = $event->message;

        // Only send read receipts for inbound WhatsApp messages
        if ($message->direction !== MessageDirectionEnum::INBOUND) {
            return;
        }

        $conversation = $message->conversation;
        if ($conversation->channel->type !== ChannelTypeEnum::WHATSAPP) {
            return;
        }

        // Send read receipt
        $this->readReceiptService->markAsRead($message);
    }
}
