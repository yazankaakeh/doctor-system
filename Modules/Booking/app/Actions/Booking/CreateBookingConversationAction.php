<?php

namespace Modules\Booking\Actions\Booking;

use Modules\Booking\Models\Booking;
use Modules\Messaging\DataTransferObjects\CreateConversationDTO;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Enums\MessageStatusEnum;
use Modules\Messaging\Enums\MessageTypeEnum;
use Modules\Messaging\Enums\SenderTypeEnum;
use Modules\Messaging\Models\Channel;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Services\ConversationService;

class CreateBookingConversationAction
{
    public function __construct(
        private readonly ConversationService $conversationService
    ) {}

    /**
     * Create a WebChat conversation for a confirmed booking.
     */
    public function handle(Booking $booking): Conversation
    {
        // Get or create the WebChat channel
        $channel = Channel::where('type', ChannelTypeEnum::WEBCHAT)
            ->where('is_active', true)
            ->first();

        if (! $channel) {
            // Create WebChat channel if it doesn't exist
            $channel = Channel::create([
                'name' => 'WebChat',
                'type' => ChannelTypeEnum::WEBCHAT,
                'is_active' => true,
                'is_admin_only' => false,
            ]);
        }

        // Build participant identifier using booking ID
        $participantIdentifier = 'booking:'.$booking->id;

        // Create conversation with booking as conversable
        $dto = CreateConversationDTO::make(
            channel: $channel,
            participantIdentifier: $participantIdentifier,
            participantName: $booking->patient->name,
            conversable: $booking,
            metadata: [
                'booking_id' => $booking->id,
                'doctor_id' => $booking->doctor_id,
                'patient_id' => $booking->patient_id,
                'doctor_name' => $booking->doctor->name,
                'patient_name' => $booking->patient->name,
                'booking_date' => $booking->booking_date->toDateString(),
                'start_time' => $booking->start_time->format('H:i'),
            ]
        );

        $conversation = $this->conversationService->create($dto);

        // Send a system welcome message
        $this->sendWelcomeMessage($conversation, $booking);

        return $conversation;
    }

    /**
     * Send a welcome message to the conversation.
     */
    protected function sendWelcomeMessage(Conversation $conversation, Booking $booking): void
    {
        $welcomeMessage = __('booking::booking.conversation_welcome', [
            'doctor' => $booking->doctor->name,
            'patient' => $booking->patient->name,
            'date' => $booking->booking_date->format('Y-m-d'),
            'time' => $booking->start_time->format('H:i'),
        ]);

        $conversation->messages()->create([
            'sender_type' => SenderTypeEnum::SYSTEM,
            'sender_id' => null,
            'direction' => MessageDirectionEnum::OUTBOUND,
            'message_type' => MessageTypeEnum::TEXT,
            'content' => $welcomeMessage,
            'status' => MessageStatusEnum::DELIVERED,
        ]);

        $conversation->update(['last_message_at' => now()]);
    }
}
