<?php

namespace Modules\Messaging\Contracts;

use Modules\Messaging\DataTransferObjects\InboundMessageDTO;
use Modules\Messaging\DataTransferObjects\MessageResultDTO;
use Modules\Messaging\DataTransferObjects\SendMessageDTO;
use Modules\Messaging\DataTransferObjects\SendTemplateDTO;
use Modules\Messaging\DataTransferObjects\WebhookPayloadDTO;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Models\Channel;

interface ChannelInterface
{
    /**
     * Get the channel type identifier.
     */
    public function getChannelType(): ChannelTypeEnum;

    /**
     * Check if the channel is properly configured.
     */
    public function isConfigured(): bool;

    /**
     * Send a text message.
     */
    public function sendMessage(SendMessageDTO $dto): MessageResultDTO;

    /**
     * Send a template message (e.g., WhatsApp templates).
     */
    public function sendTemplate(SendTemplateDTO $dto): MessageResultDTO;

    /**
     * Send a media message (image, document, audio, video).
     */
    public function sendMedia(SendMessageDTO $dto): MessageResultDTO;

    /**
     * Process an incoming webhook payload.
     */
    public function processWebhook(WebhookPayloadDTO $payload): ?InboundMessageDTO;

    /**
     * Verify webhook signature/authenticity.
     */
    public function verifyWebhook(array $headers, string $body): bool;

    /**
     * Get list of supported message types for this channel.
     */
    public function getSupportedMessageTypes(): array;

    /**
     * Check if channel supports templates.
     */
    public function supportsTemplates(): bool;

    /**
     * Get the channel model instance.
     */
    public function getChannelModel(): Channel;
}
