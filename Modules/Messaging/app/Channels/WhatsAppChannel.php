<?php

namespace Modules\Messaging\Channels;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Modules\Messaging\DataTransferObjects\InboundMessageDTO;
use Modules\Messaging\DataTransferObjects\MessageResultDTO;
use Modules\Messaging\DataTransferObjects\SendMessageDTO;
use Modules\Messaging\DataTransferObjects\SendTemplateDTO;
use Modules\Messaging\DataTransferObjects\WebhookPayloadDTO;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\MessageStatusEnum;
use Modules\Messaging\Enums\MessageTypeEnum;

class WhatsAppChannel extends AbstractChannel
{
    protected const API_BASE_URL = 'https://graph.facebook.com';

    public function getChannelType(): ChannelTypeEnum
    {
        return ChannelTypeEnum::WHATSAPP;
    }

    public function isConfigured(): bool
    {
        return ! empty($this->getConfig('access_token'))
            && ! empty($this->getConfig('phone_number_id'));
    }

    public function sendMessage(SendMessageDTO $dto): MessageResultDTO
    {
        if (! $this->isConfigured()) {
            return MessageResultDTO::failure(
                errorCode: 'NOT_CONFIGURED',
                errorMessage: 'WhatsApp channel is not configured.'
            );
        }

        $message = $this->createOutboundMessage($dto);

        try {
            $payload = $this->buildMessagePayload($dto);
            $response = $this->sendToWhatsApp($payload);

            if ($response->successful()) {
                $messageId = $response->json('messages.0.id');

                $this->updateMessageStatus($message, MessageStatusEnum::SENT, $messageId);
                $dto->conversation->updateLastMessageAt();

                return MessageResultDTO::success(
                    message: $message->fresh(),
                    externalMessageId: $messageId,
                    status: MessageStatusEnum::SENT
                );
            }

            $errorData = $response->json('error', []);
            $errorCode = $errorData['code'] ?? 'UNKNOWN';
            $errorMessage = $errorData['message'] ?? 'Unknown error';

            $this->updateMessageStatus($message, MessageStatusEnum::FAILED, null, $errorCode, $errorMessage);

            $this->log('error', 'WhatsApp message failed', [
                'error' => $errorData,
                'recipient' => $dto->getRecipientIdentifier(),
            ]);

            return MessageResultDTO::failure(
                message: $message->fresh(),
                errorCode: $errorCode,
                errorMessage: $errorMessage
            );
        } catch (\Exception $e) {
            $this->updateMessageStatus($message, MessageStatusEnum::FAILED, null, 'EXCEPTION', $e->getMessage());

            $this->log('error', 'WhatsApp exception', [
                'exception' => $e->getMessage(),
                'recipient' => $dto->getRecipientIdentifier(),
            ]);

            return MessageResultDTO::failure(
                message: $message->fresh(),
                errorCode: 'EXCEPTION',
                errorMessage: $e->getMessage()
            );
        }
    }

    public function sendTemplate(SendTemplateDTO $dto): MessageResultDTO
    {
        if (! $this->isConfigured()) {
            return MessageResultDTO::failure(
                errorCode: 'NOT_CONFIGURED',
                errorMessage: 'WhatsApp channel is not configured.'
            );
        }

        $message = $this->createTemplateMessage($dto);

        try {
            $payload = $this->buildTemplatePayload($dto);
            $response = $this->sendToWhatsApp($payload);

            if ($response->successful()) {
                $messageId = $response->json('messages.0.id');

                $this->updateMessageStatus($message, MessageStatusEnum::SENT, $messageId);
                $dto->conversation->updateLastMessageAt();

                return MessageResultDTO::success(
                    message: $message->fresh(),
                    externalMessageId: $messageId,
                    status: MessageStatusEnum::SENT
                );
            }

            $errorData = $response->json('error', []);
            $errorCode = $errorData['code'] ?? 'UNKNOWN';
            $errorMessage = $errorData['message'] ?? 'Unknown error';

            $this->updateMessageStatus($message, MessageStatusEnum::FAILED, null, $errorCode, $errorMessage);

            return MessageResultDTO::failure(
                message: $message->fresh(),
                errorCode: $errorCode,
                errorMessage: $errorMessage
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
        // Parse WhatsApp webhook structure
        $entry = $payload->get('entry.0');
        if (! $entry) {
            return null;
        }

        $changes = data_get($entry, 'changes.0');
        if (! $changes) {
            return null;
        }

        $value = data_get($changes, 'value');

        // Check for status updates first
        $statuses = data_get($value, 'statuses', []);
        if (! empty($statuses)) {
            $this->processStatusUpdate($statuses);
            return null;
        }

        $messages = data_get($value, 'messages', []);

        if (empty($messages)) {
            return null;
        }

        $messageData = $messages[0];
        $contact = data_get($value, 'contacts.0', []);

        $messageType = $this->parseMessageType($messageData['type'] ?? 'text');
        $content = $this->extractMessageContent($messageData, $messageType);

        return InboundMessageDTO::make(
            channelType: ChannelTypeEnum::WHATSAPP,
            participantIdentifier: $messageData['from'],
            content: $content,
            messageType: $messageType,
            participantName: $contact['profile']['name'] ?? null,
            externalMessageId: $messageData['id'],
            mediaUrl: $this->extractMediaUrl($messageData, $messageType),
            mimeType: $this->extractMimeType($messageData, $messageType),
            latitude: data_get($messageData, 'location.latitude'),
            longitude: data_get($messageData, 'location.longitude'),
            timestamp: isset($messageData['timestamp'])
                ? Carbon::createFromTimestamp($messageData['timestamp'])
                : now(),
            metadata: [
                'raw_type' => $messageData['type'] ?? 'text',
            ]
        );
    }

    public function verifyWebhook(array $headers, string $body): bool
    {
        $signature = $headers['x-hub-signature-256'][0] ?? $headers['X-Hub-Signature-256'] ?? null;

        if (! $signature) {
            return false;
        }

        $appSecret = $this->getConfig('app_secret');
        if (! $appSecret) {
            $this->log('warning', 'WhatsApp app_secret not configured for webhook verification');

            return true; // Allow if not configured (development mode)
        }

        $expectedSignature = 'sha256='.hash_hmac('sha256', $body, $appSecret);

        return hash_equals($expectedSignature, $signature);
    }

    // =========================================================================
    // STATUS UPDATE PROCESSING
    // =========================================================================

    /**
     * Process status updates from WhatsApp webhooks.
     * WhatsApp sends: sent, delivered, read, failed statuses
     */
    protected function processStatusUpdate(array $statuses): void
    {
        foreach ($statuses as $statusData) {
            $externalMessageId = $statusData['id'] ?? null;
            $status = $statusData['status'] ?? null;
            $timestamp = isset($statusData['timestamp'])
                ? Carbon::createFromTimestamp($statusData['timestamp'])
                : now();

            if (! $externalMessageId || ! $status) {
                continue;
            }

            // Find the message by external_message_id
            $message = \Modules\Messaging\Models\Message::where('external_message_id', $externalMessageId)->first();

            if (! $message) {
                $this->log('warning', 'Status update for unknown message', [
                    'external_message_id' => $externalMessageId,
                    'status' => $status,
                ]);
                continue;
            }

            // Map WhatsApp status to our enum
            $newStatus = $this->mapWhatsAppStatus($status);

            if (! $newStatus) {
                continue;
            }

            // Only update if it's a progression (don't go backwards)
            if ($this->shouldUpdateStatus($message->status, $newStatus)) {
                $updateData = ['status' => $newStatus];

                // Set appropriate timestamp
                switch ($newStatus) {
                    case MessageStatusEnum::SENT:
                        $updateData['sent_at'] = $timestamp;
                        break;
                    case MessageStatusEnum::DELIVERED:
                        $updateData['delivered_at'] = $timestamp;
                        break;
                    case MessageStatusEnum::READ:
                        $updateData['read_at'] = $timestamp;
                        break;
                    case MessageStatusEnum::FAILED:
                        $updateData['error_code'] = $statusData['errors'][0]['code'] ?? null;
                        $updateData['error_message'] = $statusData['errors'][0]['title'] ?? null;
                        break;
                }

                $message->update($updateData);

                $this->log('info', 'Message status updated', [
                    'message_id' => $message->id,
                    'external_message_id' => $externalMessageId,
                    'old_status' => $message->getOriginal('status')?->value ?? 'unknown',
                    'new_status' => $newStatus->value,
                ]);

                // Dispatch event for real-time updates
                event(new \Modules\Messaging\Events\MessageStatusUpdated($message));
            }
        }
    }

    /**
     * Map WhatsApp status string to our MessageStatusEnum.
     */
    protected function mapWhatsAppStatus(string $status): ?MessageStatusEnum
    {
        return match ($status) {
            'sent' => MessageStatusEnum::SENT,
            'delivered' => MessageStatusEnum::DELIVERED,
            'read' => MessageStatusEnum::READ,
            'failed' => MessageStatusEnum::FAILED,
            default => null,
        };
    }

    /**
     * Check if we should update to the new status (only allow progression).
     */
    protected function shouldUpdateStatus(MessageStatusEnum $current, MessageStatusEnum $new): bool
    {
        $order = [
            MessageStatusEnum::PENDING->value => 0,
            MessageStatusEnum::QUEUED->value => 1,
            MessageStatusEnum::SENT->value => 2,
            MessageStatusEnum::DELIVERED->value => 3,
            MessageStatusEnum::READ->value => 4,
            MessageStatusEnum::FAILED->value => 5, // Failed can happen at any point
        ];

        // Always allow transition to FAILED
        if ($new === MessageStatusEnum::FAILED) {
            return true;
        }

        return ($order[$new->value] ?? 0) > ($order[$current->value] ?? 0);
    }

    // =========================================================================
    // PROTECTED METHODS
    // =========================================================================

    protected function sendToWhatsApp(array $payload)
    {
        $apiVersion = $this->getConfig('api_version', 'v18.0');
        $phoneNumberId = $this->getConfig('phone_number_id');
        $accessToken = $this->getConfig('access_token');

        $url = self::API_BASE_URL."/{$apiVersion}/{$phoneNumberId}/messages";

        return Http::withToken($accessToken)
            ->timeout(30)
            ->post($url, $payload);
    }

    protected function buildMessagePayload(SendMessageDTO $dto): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $dto->getRecipientIdentifier(),
        ];

        switch ($dto->messageType) {
            case MessageTypeEnum::TEXT:
                $payload['type'] = 'text';
                $payload['text'] = ['body' => $dto->content];
                break;

            case MessageTypeEnum::IMAGE:
                $payload['type'] = 'image';
                $payload['image'] = $dto->mediaUrl
                    ? ['link' => $dto->mediaUrl]
                    : ['id' => $dto->metadata['media_id'] ?? ''];
                if ($dto->content) {
                    $payload['image']['caption'] = $dto->content;
                }
                break;

            case MessageTypeEnum::DOCUMENT:
                $payload['type'] = 'document';
                $payload['document'] = $dto->mediaUrl
                    ? ['link' => $dto->mediaUrl]
                    : ['id' => $dto->metadata['media_id'] ?? ''];
                if ($dto->content) {
                    $payload['document']['caption'] = $dto->content;
                }
                if ($dto->fileName) {
                    $payload['document']['filename'] = $dto->fileName;
                }
                break;

            case MessageTypeEnum::AUDIO:
                $payload['type'] = 'audio';
                $payload['audio'] = $dto->mediaUrl
                    ? ['link' => $dto->mediaUrl]
                    : ['id' => $dto->metadata['media_id'] ?? ''];
                break;

            case MessageTypeEnum::VIDEO:
                $payload['type'] = 'video';
                $payload['video'] = $dto->mediaUrl
                    ? ['link' => $dto->mediaUrl]
                    : ['id' => $dto->metadata['media_id'] ?? ''];
                if ($dto->content) {
                    $payload['video']['caption'] = $dto->content;
                }
                break;

            case MessageTypeEnum::LOCATION:
                $payload['type'] = 'location';
                $payload['location'] = [
                    'latitude' => $dto->metadata['latitude'] ?? 0,
                    'longitude' => $dto->metadata['longitude'] ?? 0,
                    'name' => $dto->content,
                ];
                break;
        }

        return $payload;
    }

    protected function buildTemplatePayload(SendTemplateDTO $dto): array
    {
        $components = [];

        // Build header component if template has header with media
        if ($dto->hasHeaderMedia() && $dto->template->header_type !== 'text') {
            $headerComponent = [
                'type' => 'header',
                'parameters' => [
                    [
                        'type' => $dto->template->header_type, // image, document, video
                        $dto->template->header_type => ['link' => $dto->headerMediaUrl],
                    ],
                ],
            ];
            $components[] = $headerComponent;
        }

        // Build body component with variables
        if (! empty($dto->variables)) {
            $bodyParameters = [];
            foreach ($dto->variables as $value) {
                $bodyParameters[] = [
                    'type' => 'text',
                    'text' => (string) $value,
                ];
            }

            $components[] = [
                'type' => 'body',
                'parameters' => $bodyParameters,
            ];
        }

        return [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $dto->getRecipientIdentifier(),
            'type' => 'template',
            'template' => [
                'name' => $dto->template->external_template_id ?? $dto->template->name,
                'language' => ['code' => $dto->template->language],
                'components' => $components,
            ],
        ];
    }

    protected function parseMessageType(string $type): MessageTypeEnum
    {
        return match ($type) {
            'text' => MessageTypeEnum::TEXT,
            'image' => MessageTypeEnum::IMAGE,
            'document' => MessageTypeEnum::DOCUMENT,
            'audio', 'voice' => MessageTypeEnum::AUDIO,
            'video' => MessageTypeEnum::VIDEO,
            'location' => MessageTypeEnum::LOCATION,
            'sticker' => MessageTypeEnum::STICKER,
            'contacts' => MessageTypeEnum::CONTACT,
            default => MessageTypeEnum::TEXT,
        };
    }

    protected function extractMessageContent(array $messageData, MessageTypeEnum $type): string
    {
        return match ($type) {
            MessageTypeEnum::TEXT => $messageData['text']['body'] ?? '',
            MessageTypeEnum::IMAGE => $messageData['image']['caption'] ?? '[Image]',
            MessageTypeEnum::DOCUMENT => $messageData['document']['caption'] ?? $messageData['document']['filename'] ?? '[Document]',
            MessageTypeEnum::AUDIO => '[Audio message]',
            MessageTypeEnum::VIDEO => $messageData['video']['caption'] ?? '[Video]',
            MessageTypeEnum::LOCATION => $messageData['location']['name'] ?? '[Location]',
            MessageTypeEnum::STICKER => '[Sticker]',
            MessageTypeEnum::CONTACT => '[Contact]',
            default => '',
        };
    }

    protected function extractMediaUrl(array $messageData, MessageTypeEnum $type): ?string
    {
        // WhatsApp provides media IDs, not URLs directly
        // You need to make a separate API call to get the actual URL
        $mediaId = match ($type) {
            MessageTypeEnum::IMAGE => $messageData['image']['id'] ?? null,
            MessageTypeEnum::DOCUMENT => $messageData['document']['id'] ?? null,
            MessageTypeEnum::AUDIO => $messageData['audio']['id'] ?? null,
            MessageTypeEnum::VIDEO => $messageData['video']['id'] ?? null,
            MessageTypeEnum::STICKER => $messageData['sticker']['id'] ?? null,
            default => null,
        };

        // Store media ID in metadata, URL can be fetched separately
        return $mediaId;
    }

    protected function extractMimeType(array $messageData, MessageTypeEnum $type): ?string
    {
        return match ($type) {
            MessageTypeEnum::IMAGE => $messageData['image']['mime_type'] ?? 'image/jpeg',
            MessageTypeEnum::DOCUMENT => $messageData['document']['mime_type'] ?? null,
            MessageTypeEnum::AUDIO => $messageData['audio']['mime_type'] ?? 'audio/ogg',
            MessageTypeEnum::VIDEO => $messageData['video']['mime_type'] ?? 'video/mp4',
            default => null,
        };
    }

    /**
     * Download media from WhatsApp.
     */
    public function downloadMedia(string $mediaId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $apiVersion = $this->getConfig('api_version', 'v18.0');
        $accessToken = $this->getConfig('access_token');

        // First, get the media URL
        $response = Http::withToken($accessToken)
            ->get(self::API_BASE_URL."/{$apiVersion}/{$mediaId}");

        if (! $response->successful()) {
            return null;
        }

        $mediaUrl = $response->json('url');
        $mimeType = $response->json('mime_type');

        // Then download the actual file
        $fileResponse = Http::withToken($accessToken)->get($mediaUrl);

        if (! $fileResponse->successful()) {
            return null;
        }

        return [
            'content' => $fileResponse->body(),
            'mime_type' => $mimeType,
        ];
    }
}
