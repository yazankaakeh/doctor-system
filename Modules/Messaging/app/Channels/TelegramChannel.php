<?php

namespace Modules\Messaging\Channels;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Modules\Messaging\DataTransferObjects\InboundMessageDTO;
use Modules\Messaging\DataTransferObjects\MessageResultDTO;
use Modules\Messaging\DataTransferObjects\SendMessageDTO;
use Modules\Messaging\DataTransferObjects\WebhookPayloadDTO;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\MessageStatusEnum;
use Modules\Messaging\Enums\MessageTypeEnum;

class TelegramChannel extends AbstractChannel
{
    protected const API_BASE_URL = 'https://api.telegram.org/bot';

    public function getChannelType(): ChannelTypeEnum
    {
        return ChannelTypeEnum::TELEGRAM;
    }

    public function isConfigured(): bool
    {
        return ! empty($this->getConfig('bot_token'));
    }

    public function sendMessage(SendMessageDTO $dto): MessageResultDTO
    {
        if (! $this->isConfigured()) {
            return MessageResultDTO::failure(
                errorCode: 'NOT_CONFIGURED',
                errorMessage: 'Telegram channel is not configured.'
            );
        }

        $message = $this->createOutboundMessage($dto);

        try {
            $response = match ($dto->messageType) {
                MessageTypeEnum::TEXT => $this->sendTextMessage($dto),
                MessageTypeEnum::IMAGE => $this->sendPhoto($dto),
                MessageTypeEnum::DOCUMENT => $this->sendDocument($dto),
                MessageTypeEnum::AUDIO => $this->sendAudio($dto),
                MessageTypeEnum::VIDEO => $this->sendVideo($dto),
                MessageTypeEnum::LOCATION => $this->sendLocation($dto),
                default => $this->sendTextMessage($dto),
            };

            if ($response->successful() && $response->json('ok')) {
                $result = $response->json('result');
                $messageId = (string) ($result['message_id'] ?? '');

                $this->updateMessageStatus($message, MessageStatusEnum::SENT, $messageId);
                $dto->conversation->updateLastMessageAt();

                return MessageResultDTO::success(
                    message: $message->fresh(),
                    externalMessageId: $messageId,
                    status: MessageStatusEnum::SENT
                );
            }

            $errorDescription = $response->json('description', 'Unknown error');
            $errorCode = (string) ($response->json('error_code', 'UNKNOWN'));

            $this->updateMessageStatus($message, MessageStatusEnum::FAILED, null, $errorCode, $errorDescription);

            $this->log('error', 'Telegram message failed', [
                'error' => $errorDescription,
                'recipient' => $dto->getRecipientIdentifier(),
            ]);

            return MessageResultDTO::failure(
                message: $message->fresh(),
                errorCode: $errorCode,
                errorMessage: $errorDescription
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
        $update = $payload->payload;

        // Handle message updates
        $messageData = $update['message'] ?? $update['edited_message'] ?? null;

        if (! $messageData) {
            return null;
        }

        $from = $messageData['from'] ?? [];
        $chat = $messageData['chat'] ?? [];

        // Determine message type and extract content
        $messageType = $this->parseMessageType($messageData);
        $content = $this->extractMessageContent($messageData, $messageType);

        // Build participant identifier (chat_id for sending replies)
        $participantIdentifier = (string) $chat['id'];

        // Build participant name
        $participantName = trim(($from['first_name'] ?? '').' '.($from['last_name'] ?? ''));
        if (empty($participantName)) {
            $participantName = $from['username'] ?? 'Unknown';
        }

        return InboundMessageDTO::make(
            channelType: ChannelTypeEnum::TELEGRAM,
            participantIdentifier: $participantIdentifier,
            content: $content,
            messageType: $messageType,
            participantName: $participantName,
            externalMessageId: (string) $messageData['message_id'],
            mediaUrl: $this->extractFileId($messageData, $messageType),
            mimeType: $this->extractMimeType($messageData, $messageType),
            latitude: $messageData['location']['latitude'] ?? null,
            longitude: $messageData['location']['longitude'] ?? null,
            timestamp: isset($messageData['date'])
                ? Carbon::createFromTimestamp($messageData['date'])
                : now(),
            metadata: [
                'chat_type' => $chat['type'] ?? 'private',
                'from_username' => $from['username'] ?? null,
                'from_id' => $from['id'] ?? null,
            ]
        );
    }

    public function verifyWebhook(array $headers, string $body): bool
    {
        // Telegram doesn't sign webhooks, but you can verify via secret token in URL
        // For now, we'll verify using an optional secret token header
        $secretToken = $this->getConfig('webhook_secret');

        if (! $secretToken) {
            return true; // Allow if not configured
        }

        $headerToken = $headers['x-telegram-bot-api-secret-token'][0]
            ?? $headers['X-Telegram-Bot-Api-Secret-Token']
            ?? null;

        return $headerToken === $secretToken;
    }

    // =========================================================================
    // PROTECTED METHODS
    // =========================================================================

    protected function getApiUrl(string $method): string
    {
        return self::API_BASE_URL.$this->getConfig('bot_token').'/'.$method;
    }

    protected function sendTextMessage(SendMessageDTO $dto)
    {
        return Http::timeout(30)->post($this->getApiUrl('sendMessage'), [
            'chat_id' => $dto->getRecipientIdentifier(),
            'text' => $dto->content,
            'parse_mode' => 'HTML',
        ]);
    }

    protected function sendPhoto(SendMessageDTO $dto)
    {
        $data = [
            'chat_id' => $dto->getRecipientIdentifier(),
        ];

        if ($dto->mediaUrl) {
            $data['photo'] = $dto->mediaUrl;
        } elseif ($dto->metadata['file_id'] ?? null) {
            $data['photo'] = $dto->metadata['file_id'];
        }

        if ($dto->content) {
            $data['caption'] = $dto->content;
            $data['parse_mode'] = 'HTML';
        }

        return Http::timeout(30)->post($this->getApiUrl('sendPhoto'), $data);
    }

    protected function sendDocument(SendMessageDTO $dto)
    {
        $data = [
            'chat_id' => $dto->getRecipientIdentifier(),
        ];

        if ($dto->mediaUrl) {
            $data['document'] = $dto->mediaUrl;
        } elseif ($dto->metadata['file_id'] ?? null) {
            $data['document'] = $dto->metadata['file_id'];
        }

        if ($dto->content) {
            $data['caption'] = $dto->content;
            $data['parse_mode'] = 'HTML';
        }

        return Http::timeout(30)->post($this->getApiUrl('sendDocument'), $data);
    }

    protected function sendAudio(SendMessageDTO $dto)
    {
        $data = [
            'chat_id' => $dto->getRecipientIdentifier(),
        ];

        if ($dto->mediaUrl) {
            $data['audio'] = $dto->mediaUrl;
        } elseif ($dto->metadata['file_id'] ?? null) {
            $data['audio'] = $dto->metadata['file_id'];
        }

        if ($dto->content) {
            $data['caption'] = $dto->content;
            $data['parse_mode'] = 'HTML';
        }

        return Http::timeout(30)->post($this->getApiUrl('sendAudio'), $data);
    }

    protected function sendVideo(SendMessageDTO $dto)
    {
        $data = [
            'chat_id' => $dto->getRecipientIdentifier(),
        ];

        if ($dto->mediaUrl) {
            $data['video'] = $dto->mediaUrl;
        } elseif ($dto->metadata['file_id'] ?? null) {
            $data['video'] = $dto->metadata['file_id'];
        }

        if ($dto->content) {
            $data['caption'] = $dto->content;
            $data['parse_mode'] = 'HTML';
        }

        return Http::timeout(30)->post($this->getApiUrl('sendVideo'), $data);
    }

    protected function sendLocation(SendMessageDTO $dto)
    {
        return Http::timeout(30)->post($this->getApiUrl('sendLocation'), [
            'chat_id' => $dto->getRecipientIdentifier(),
            'latitude' => $dto->metadata['latitude'] ?? 0,
            'longitude' => $dto->metadata['longitude'] ?? 0,
        ]);
    }

    protected function parseMessageType(array $messageData): MessageTypeEnum
    {
        if (isset($messageData['text'])) {
            return MessageTypeEnum::TEXT;
        }
        if (isset($messageData['photo'])) {
            return MessageTypeEnum::IMAGE;
        }
        if (isset($messageData['document'])) {
            return MessageTypeEnum::DOCUMENT;
        }
        if (isset($messageData['audio']) || isset($messageData['voice'])) {
            return MessageTypeEnum::AUDIO;
        }
        if (isset($messageData['video']) || isset($messageData['video_note'])) {
            return MessageTypeEnum::VIDEO;
        }
        if (isset($messageData['location'])) {
            return MessageTypeEnum::LOCATION;
        }
        if (isset($messageData['sticker'])) {
            return MessageTypeEnum::STICKER;
        }
        if (isset($messageData['contact'])) {
            return MessageTypeEnum::CONTACT;
        }

        return MessageTypeEnum::TEXT;
    }

    protected function extractMessageContent(array $messageData, MessageTypeEnum $type): string
    {
        return match ($type) {
            MessageTypeEnum::TEXT => $messageData['text'] ?? '',
            MessageTypeEnum::IMAGE => $messageData['caption'] ?? '[Photo]',
            MessageTypeEnum::DOCUMENT => $messageData['caption'] ?? $messageData['document']['file_name'] ?? '[Document]',
            MessageTypeEnum::AUDIO => $messageData['caption'] ?? '[Audio]',
            MessageTypeEnum::VIDEO => $messageData['caption'] ?? '[Video]',
            MessageTypeEnum::LOCATION => '[Location]',
            MessageTypeEnum::STICKER => $messageData['sticker']['emoji'] ?? '[Sticker]',
            MessageTypeEnum::CONTACT => $messageData['contact']['phone_number'] ?? '[Contact]',
            default => '',
        };
    }

    protected function extractFileId(array $messageData, MessageTypeEnum $type): ?string
    {
        return match ($type) {
            MessageTypeEnum::IMAGE => $this->getLargestPhotoFileId($messageData['photo'] ?? []),
            MessageTypeEnum::DOCUMENT => $messageData['document']['file_id'] ?? null,
            MessageTypeEnum::AUDIO => $messageData['audio']['file_id'] ?? $messageData['voice']['file_id'] ?? null,
            MessageTypeEnum::VIDEO => $messageData['video']['file_id'] ?? $messageData['video_note']['file_id'] ?? null,
            MessageTypeEnum::STICKER => $messageData['sticker']['file_id'] ?? null,
            default => null,
        };
    }

    protected function getLargestPhotoFileId(array $photos): ?string
    {
        if (empty($photos)) {
            return null;
        }

        // Photos are sorted by size, last is largest
        $largest = end($photos);

        return $largest['file_id'] ?? null;
    }

    protected function extractMimeType(array $messageData, MessageTypeEnum $type): ?string
    {
        return match ($type) {
            MessageTypeEnum::DOCUMENT => $messageData['document']['mime_type'] ?? null,
            MessageTypeEnum::AUDIO => $messageData['audio']['mime_type'] ?? $messageData['voice']['mime_type'] ?? null,
            MessageTypeEnum::VIDEO => $messageData['video']['mime_type'] ?? null,
            default => null,
        };
    }

    /**
     * Get file URL from Telegram.
     */
    public function getFileUrl(string $fileId): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $response = Http::timeout(30)->post($this->getApiUrl('getFile'), [
            'file_id' => $fileId,
        ]);

        if (! $response->successful() || ! $response->json('ok')) {
            return null;
        }

        $filePath = $response->json('result.file_path');

        return 'https://api.telegram.org/file/bot'.$this->getConfig('bot_token').'/'.$filePath;
    }

    /**
     * Set webhook URL for this bot.
     */
    public function setWebhook(string $url, ?string $secretToken = null): bool
    {
        $data = ['url' => $url];

        if ($secretToken) {
            $data['secret_token'] = $secretToken;
        }

        $response = Http::timeout(30)->post($this->getApiUrl('setWebhook'), $data);

        return $response->successful() && $response->json('ok');
    }
}
