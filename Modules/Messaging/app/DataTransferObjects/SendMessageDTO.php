<?php

namespace Modules\Messaging\DataTransferObjects;

use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\MessageTypeEnum;
use Modules\Messaging\Models\Conversation;

class SendMessageDTO
{
    public function __construct(
        public readonly Conversation $conversation,
        public readonly string $content,
        public readonly MessageTypeEnum $messageType = MessageTypeEnum::TEXT,
        public readonly ?int $senderId = null,
        public readonly ?string $mediaUrl = null,
        public readonly ?string $mediaPath = null,
        public readonly ?string $mimeType = null,
        public readonly ?string $fileName = null,
        public readonly array $metadata = [],
    ) {}

    public static function make(
        Conversation $conversation,
        string $content,
        MessageTypeEnum $messageType = MessageTypeEnum::TEXT,
        ?int $senderId = null,
        ?string $mediaUrl = null,
        ?string $mediaPath = null,
        ?string $mimeType = null,
        ?string $fileName = null,
        array $metadata = [],
    ): self {
        return new self(
            conversation: $conversation,
            content: $content,
            messageType: $messageType,
            senderId: $senderId,
            mediaUrl: $mediaUrl,
            mediaPath: $mediaPath,
            mimeType: $mimeType,
            fileName: $fileName,
            metadata: $metadata,
        );
    }

    public function getChannelType(): ChannelTypeEnum
    {
        return $this->conversation->channel->type;
    }

    public function getRecipientIdentifier(): string
    {
        return $this->conversation->participant_identifier;
    }

    public function hasMedia(): bool
    {
        return $this->mediaUrl !== null || $this->mediaPath !== null;
    }

    public function toArray(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'content' => $this->content,
            'message_type' => $this->messageType->value,
            'sender_id' => $this->senderId,
            'media_url' => $this->mediaUrl,
            'media_path' => $this->mediaPath,
            'mime_type' => $this->mimeType,
            'file_name' => $this->fileName,
            'metadata' => $this->metadata,
        ];
    }
}
