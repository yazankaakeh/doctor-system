<?php

namespace Modules\Messaging\DataTransferObjects;

use Modules\Messaging\Enums\MessageStatusEnum;
use Modules\Messaging\Models\Message;

class MessageResultDTO
{
    public function __construct(
        public readonly bool $success,
        public readonly ?Message $message = null,
        public readonly ?string $externalMessageId = null,
        public readonly MessageStatusEnum $status = MessageStatusEnum::PENDING,
        public readonly ?string $errorCode = null,
        public readonly ?string $errorMessage = null,
        public readonly array $metadata = [],
    ) {}

    public static function success(
        Message $message,
        ?string $externalMessageId = null,
        MessageStatusEnum $status = MessageStatusEnum::SENT,
        array $metadata = [],
    ): self {
        return new self(
            success: true,
            message: $message,
            externalMessageId: $externalMessageId,
            status: $status,
            metadata: $metadata,
        );
    }

    public static function failure(
        ?Message $message = null,
        ?string $errorCode = null,
        ?string $errorMessage = null,
        array $metadata = [],
    ): self {
        return new self(
            success: false,
            message: $message,
            status: MessageStatusEnum::FAILED,
            errorCode: $errorCode,
            errorMessage: $errorMessage,
            metadata: $metadata,
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return ! $this->success;
    }

    public function hasMessage(): bool
    {
        return $this->message !== null;
    }

    public function getError(): ?string
    {
        if ($this->errorMessage) {
            return $this->errorCode
                ? "[{$this->errorCode}] {$this->errorMessage}"
                : $this->errorMessage;
        }

        return $this->errorCode;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message_id' => $this->message?->id,
            'external_message_id' => $this->externalMessageId,
            'status' => $this->status->value,
            'error_code' => $this->errorCode,
            'error_message' => $this->errorMessage,
            'metadata' => $this->metadata,
        ];
    }
}
