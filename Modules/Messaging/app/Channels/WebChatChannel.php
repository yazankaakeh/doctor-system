<?php

namespace Modules\Messaging\Channels;

use Modules\Messaging\DataTransferObjects\InboundMessageDTO;
use Modules\Messaging\DataTransferObjects\MessageResultDTO;
use Modules\Messaging\DataTransferObjects\SendMessageDTO;
use Modules\Messaging\DataTransferObjects\WebhookPayloadDTO;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\MessageStatusEnum;
use Modules\Messaging\Enums\MessageTypeEnum;
use Modules\Messaging\Events\NewMessageEvent;
use Modules\Messaging\Models\Message;

class WebChatChannel extends AbstractChannel
{
    public function getChannelType(): ChannelTypeEnum
    {
        return ChannelTypeEnum::WEBCHAT;
    }

    public function isConfigured(): bool
    {
        // WebChat is always configured as it doesn't require external services
        return true;
    }

    public function sendMessage(SendMessageDTO $dto): MessageResultDTO
    {
        // Validate message type - WebChat supports all media types
        $supportedTypes = [
            MessageTypeEnum::TEXT,
            MessageTypeEnum::IMAGE,
            MessageTypeEnum::DOCUMENT,
            MessageTypeEnum::AUDIO,
            MessageTypeEnum::VIDEO,
        ];
        if (! in_array($dto->messageType, $supportedTypes)) {
            return MessageResultDTO::failure(
                errorCode: 'UNSUPPORTED_TYPE',
                errorMessage: "WebChat does not support message type: {$dto->messageType->value}"
            );
        }

        $message = $this->createOutboundMessage($dto);

        try {
            // For webchat, we just need to store the message and broadcast
            $this->updateMessageStatus($message, MessageStatusEnum::SENT);
            $dto->conversation->updateLastMessageAt();
            $dto->conversation->incrementUnread();

            // Broadcast to the websocket channel
            event(new NewMessageEvent($message->fresh()));

            return MessageResultDTO::success(
                message: $message->fresh(),
                externalMessageId: $message->uuid,
                status: MessageStatusEnum::SENT
            );
        } catch (\Exception $e) {
            $this->updateMessageStatus($message, MessageStatusEnum::FAILED, null, 'EXCEPTION', $e->getMessage());

            return MessageResultDTO::failure(
                message: $message->fresh(),
                errorCode: 'EXCEPTION',
                errorMessage: $e->getMessage()
            );
        }
    }

    public function processWebhook(WebhookPayloadDTO $payload): ?InboundMessageDTO
    {
        // WebChat doesn't use webhooks - messages come directly through the application
        // This method is here for interface compliance

        $content = $payload->get('content');
        $participantId = $payload->get('participant_id');
        $participantName = $payload->get('participant_name');

        if (! $content || ! $participantId) {
            return null;
        }

        $messageType = match ($payload->get('type', 'text')) {
            'image' => MessageTypeEnum::IMAGE,
            'document' => MessageTypeEnum::DOCUMENT,
            'audio' => MessageTypeEnum::AUDIO,
            'video' => MessageTypeEnum::VIDEO,
            default => MessageTypeEnum::TEXT,
        };

        return InboundMessageDTO::make(
            channelType: ChannelTypeEnum::WEBCHAT,
            participantIdentifier: $participantId,
            content: $content,
            messageType: $messageType,
            participantName: $participantName,
            externalMessageId: $payload->get('message_id'),
            mediaUrl: $payload->get('media_url'),
            mimeType: $payload->get('mime_type'),
            fileName: $payload->get('file_name'),
            metadata: $payload->get('metadata', [])
        );
    }

    public function verifyWebhook(array $headers, string $body): bool
    {
        // WebChat doesn't use webhooks from external services
        // Verification is done via CSRF tokens in the main application
        return true;
    }

    /**
     * Process an inbound message from the chat widget.
     * This is called directly from the application, not via webhook.
     */
    public function receiveMessage(
        string $participantIdentifier,
        string $content,
        MessageTypeEnum $messageType = MessageTypeEnum::TEXT,
        ?string $participantName = null,
        ?string $mediaUrl = null,
        ?string $mimeType = null,
        ?string $fileName = null,
        array $metadata = []
    ): InboundMessageDTO {
        return InboundMessageDTO::make(
            channelType: ChannelTypeEnum::WEBCHAT,
            participantIdentifier: $participantIdentifier,
            content: $content,
            messageType: $messageType,
            participantName: $participantName,
            mediaUrl: $mediaUrl,
            mimeType: $mimeType,
            fileName: $fileName,
            metadata: $metadata,
            timestamp: now()
        );
    }

    /**
     * Mark a message as delivered (viewed by agent).
     */
    public function markAsDelivered(int $messageId): void
    {
        $message = Message::find($messageId);

        if ($message && $message->status === MessageStatusEnum::SENT) {
            $message->markAsDelivered();
        }
    }

    /**
     * Mark a message as read.
     */
    public function markAsRead(int $messageId): void
    {
        $message = Message::find($messageId);

        if ($message && $message->status !== MessageStatusEnum::READ) {
            $message->markAsRead();
        }
    }
}
