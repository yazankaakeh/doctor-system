<?php

namespace Modules\Messaging\Channels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Messaging\Contracts\ChannelInterface;
use Modules\Messaging\DataTransferObjects\InboundMessageDTO;
use Modules\Messaging\DataTransferObjects\MessageResultDTO;
use Modules\Messaging\DataTransferObjects\SendMessageDTO;
use Modules\Messaging\DataTransferObjects\SendTemplateDTO;
use Modules\Messaging\DataTransferObjects\WebhookPayloadDTO;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Enums\MessageStatusEnum;
use Modules\Messaging\Enums\MessageTypeEnum;
use Modules\Messaging\Enums\SenderTypeEnum;
use Modules\Messaging\Models\Channel;
use Modules\Messaging\Models\Message;

abstract class AbstractChannel implements ChannelInterface
{
    protected Channel $channel;

    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    abstract public function getChannelType(): ChannelTypeEnum;

    abstract public function isConfigured(): bool;

    abstract public function sendMessage(SendMessageDTO $dto): MessageResultDTO;

    abstract public function processWebhook(WebhookPayloadDTO $payload): ?InboundMessageDTO;

    abstract public function verifyWebhook(array $headers, string $body): bool;

    public function sendTemplate(SendTemplateDTO $dto): MessageResultDTO
    {
        // Default implementation - templates not supported
        return MessageResultDTO::failure(
            errorCode: 'TEMPLATES_NOT_SUPPORTED',
            errorMessage: 'This channel does not support template messages.'
        );
    }

    public function sendMedia(SendMessageDTO $dto): MessageResultDTO
    {
        // Default to sendMessage for channels that handle media in sendMessage
        return $this->sendMessage($dto);
    }

    public function getSupportedMessageTypes(): array
    {
        return $this->getChannelType()->supportedMessageTypes();
    }

    public function supportsTemplates(): bool
    {
        return $this->getChannelType()->supportsTemplates();
    }

    public function getChannelModel(): Channel
    {
        return $this->channel;
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    protected function getConfig(string $key, $default = null)
    {
        return $this->channel->getConfigValue($key, $default);
    }

    protected function createOutboundMessage(SendMessageDTO $dto): Message
    {
        $metadata = $dto->metadata;

        // Add file metadata if present
        if ($dto->mimeType) {
            $metadata['mime_type'] = $dto->mimeType;
        }
        if ($dto->fileName) {
            $metadata['file_name'] = $dto->fileName;
        }
        if ($dto->mediaPath) {
            $metadata['media_path'] = $dto->mediaPath;
        }

        return Message::create([
            'conversation_id' => $dto->conversation->id,
            'sender_type' => SenderTypeEnum::USER,
            'sender_id' => $dto->senderId,
            'direction' => MessageDirectionEnum::OUTBOUND,
            'message_type' => $dto->messageType,
            'content' => $dto->content,
            'media_url' => $dto->mediaUrl,
            'status' => MessageStatusEnum::PENDING,
            'metadata' => $metadata,
        ]);
    }

    protected function createTemplateMessage(SendTemplateDTO $dto): Message
    {
        return Message::create([
            'conversation_id' => $dto->conversation->id,
            'sender_type' => SenderTypeEnum::USER,
            'sender_id' => $dto->senderId,
            'direction' => MessageDirectionEnum::OUTBOUND,
            'message_type' => MessageTypeEnum::TEMPLATE,
            'content' => $dto->getResolvedContent(),
            'template_id' => $dto->template->id,
            'template_variables' => $dto->variables,
            'status' => MessageStatusEnum::PENDING,
            'metadata' => $dto->metadata,
        ]);
    }

    protected function updateMessageStatus(
        Message $message,
        MessageStatusEnum $status,
        ?string $externalId = null,
        ?string $errorCode = null,
        ?string $errorMessage = null
    ): void {
        $data = ['status' => $status];

        if ($externalId) {
            $data['external_message_id'] = $externalId;
        }

        if ($status === MessageStatusEnum::SENT) {
            $data['sent_at'] = now();
        }

        if ($status === MessageStatusEnum::FAILED) {
            $data['error_code'] = $errorCode;
            $data['error_message'] = $errorMessage;
        }

        $message->update($data);
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        $context['channel'] = $this->getChannelType()->value;
        Log::channel('messaging')->{$level}($message, $context);
    }

    protected function makeHttpClient()
    {
        return Http::timeout(30)->retry(3, 100);
    }
}
