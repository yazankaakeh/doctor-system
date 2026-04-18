<?php

namespace Modules\Messaging\DataTransferObjects;

use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\MessageTypeEnum;

class InboundMessageDTO
{
    public function __construct(
        public readonly ChannelTypeEnum $channelType,
        public readonly string $participantIdentifier,
        public readonly string $content,
        public readonly MessageTypeEnum $messageType = MessageTypeEnum::TEXT,
        public readonly ?string $participantName = null,
        public readonly ?string $externalMessageId = null,
        public readonly ?string $externalConversationId = null,
        public readonly ?string $mediaUrl = null,
        public readonly ?string $mimeType = null,
        public readonly ?string $fileName = null,
        public readonly ?int $fileSize = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly array $metadata = [],
        public readonly ?\DateTimeInterface $timestamp = null,
    ) {}

    public static function make(
        ChannelTypeEnum $channelType,
        string $participantIdentifier,
        string $content,
        MessageTypeEnum $messageType = MessageTypeEnum::TEXT,
        ?string $participantName = null,
        ?string $externalMessageId = null,
        ?string $externalConversationId = null,
        ?string $mediaUrl = null,
        ?string $mimeType = null,
        ?string $fileName = null,
        ?int $fileSize = null,
        ?float $latitude = null,
        ?float $longitude = null,
        array $metadata = [],
        ?\DateTimeInterface $timestamp = null,
    ): self {
        return new self(
            channelType: $channelType,
            participantIdentifier: $participantIdentifier,
            content: $content,
            messageType: $messageType,
            participantName: $participantName,
            externalMessageId: $externalMessageId,
            externalConversationId: $externalConversationId,
            mediaUrl: $mediaUrl,
            mimeType: $mimeType,
            fileName: $fileName,
            fileSize: $fileSize,
            latitude: $latitude,
            longitude: $longitude,
            metadata: $metadata,
            timestamp: $timestamp,
        );
    }

    public function hasMedia(): bool
    {
        return $this->mediaUrl !== null;
    }

    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function isText(): bool
    {
        return $this->messageType === MessageTypeEnum::TEXT;
    }

    public function toArray(): array
    {
        return [
            'channel_type' => $this->channelType->value,
            'participant_identifier' => $this->participantIdentifier,
            'participant_name' => $this->participantName,
            'content' => $this->content,
            'message_type' => $this->messageType->value,
            'external_message_id' => $this->externalMessageId,
            'external_conversation_id' => $this->externalConversationId,
            'media_url' => $this->mediaUrl,
            'mime_type' => $this->mimeType,
            'file_name' => $this->fileName,
            'file_size' => $this->fileSize,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'metadata' => $this->metadata,
            'timestamp' => $this->timestamp?->format('Y-m-d H:i:s'),
        ];
    }
}
