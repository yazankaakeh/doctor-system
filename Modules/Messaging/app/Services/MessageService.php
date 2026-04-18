<?php

namespace Modules\Messaging\Services;

use Illuminate\Support\Collection;
use Modules\Messaging\Channels\ChannelFactory;
use Modules\Messaging\Contracts\Repositories\MessageRepositoryInterface;
use Modules\Messaging\DataTransferObjects\InboundMessageDTO;
use Modules\Messaging\DataTransferObjects\MessageResultDTO;
use Modules\Messaging\DataTransferObjects\SendMessageDTO;
use Modules\Messaging\DataTransferObjects\SendTemplateDTO;
use Modules\Messaging\Enums\ConversationStatusEnum;
use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Enums\MessageStatusEnum;
use Modules\Messaging\Enums\SenderTypeEnum;
use Modules\Messaging\Events\NewMessageEvent;
use Modules\Messaging\Jobs\SendMessageJob;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;

class MessageService
{
    public function __construct(
        protected MessageRepositoryInterface $repository,
        protected ConversationService $conversationService,
        protected ?RateLimitService $rateLimitService = null
    ) {
        $this->rateLimitService = $rateLimitService ?? app(RateLimitService::class);
    }

    /**
     * Send a message through the appropriate channel.
     */
    public function send(SendMessageDTO $dto, bool $async = true): MessageResultDTO
    {
        // Check rate limits
        if (config('messaging.rate_limits.enabled', true)) {
            if (! $this->rateLimitService->canSend($dto->conversation)) {
                return MessageResultDTO::failure(
                    errorCode: 'RATE_LIMIT_EXCEEDED',
                    errorMessage: 'Rate limit exceeded. Please wait before sending more messages.'
                );
            }
        }

        if ($async) {
            // Create the message first
            $message = $this->repository->create([
                'conversation_id' => $dto->conversation->id,
                'sender_type' => SenderTypeEnum::USER,
                'sender_id' => $dto->senderId,
                'direction' => MessageDirectionEnum::OUTBOUND,
                'message_type' => $dto->messageType,
                'content' => $dto->content,
                'media_url' => $dto->mediaUrl,
                'status' => MessageStatusEnum::QUEUED,
                'metadata' => $dto->metadata,
            ]);

            // Record send for rate limiting
            if (config('messaging.rate_limits.enabled', true)) {
                $this->rateLimitService->recordSend($dto->conversation);
            }

            // Dispatch job
            SendMessageJob::dispatch($message->id, $dto->toArray());

            return MessageResultDTO::success(
                message: $message,
                status: MessageStatusEnum::QUEUED
            );
        }

        // Send synchronously
        $channel = ChannelFactory::make($dto->conversation->channel);
        $result = $channel->sendMessage($dto);

        // Record send for rate limiting
        if ($result->success && config('messaging.rate_limits.enabled', true)) {
            $this->rateLimitService->recordSend($dto->conversation);
        }

        return $result;
    }

    /**
     * Send a template message.
     */
    public function sendTemplate(SendTemplateDTO $dto, bool $async = true): MessageResultDTO
    {
        $channel = ChannelFactory::make($dto->conversation->channel);

        if (! $channel->supportsTemplates()) {
            return MessageResultDTO::failure(
                errorCode: 'TEMPLATES_NOT_SUPPORTED',
                errorMessage: 'This channel does not support template messages.'
            );
        }

        // Check template rate limit
        if (config('messaging.rate_limits.enabled', true)) {
            if (! $this->rateLimitService->canSendTemplate($dto->conversation->channel->type)) {
                return MessageResultDTO::failure(
                    errorCode: 'TEMPLATE_RATE_LIMIT_EXCEEDED',
                    errorMessage: 'Daily template message limit exceeded.'
                );
            }
        }

        $result = $channel->sendTemplate($dto);

        // Record template send for rate limiting
        if ($result->success && config('messaging.rate_limits.enabled', true)) {
            $this->rateLimitService->recordTemplateSend();
        }

        return $result;
    }

    /**
     * Process an inbound message.
     */
    public function processInbound(InboundMessageDTO $dto): Message
    {
        // Find or create conversation
        $conversation = $this->conversationService->findOrCreate(
            $dto->participantIdentifier,
            $dto->channelType,
            null,
            $dto->participantName
        );

        // Create the message
        $message = $this->repository->create([
            'conversation_id' => $conversation->id,
            'sender_type' => SenderTypeEnum::CONTACT,
            'sender_id' => null,
            'direction' => MessageDirectionEnum::INBOUND,
            'message_type' => $dto->messageType,
            'content' => $dto->content,
            'media_url' => $dto->mediaUrl,
            'external_message_id' => $dto->externalMessageId,
            'status' => MessageStatusEnum::DELIVERED,
            'metadata' => array_merge($dto->metadata, [
                'mime_type' => $dto->mimeType,
                'file_name' => $dto->fileName,
                'file_size' => $dto->fileSize,
                'latitude' => $dto->latitude,
                'longitude' => $dto->longitude,
            ]),
        ]);

        // Update conversation
        $conversation->update([
            'last_message_at' => now(),
            'status' => $conversation->status === ConversationStatusEnum::CLOSED
                ? ConversationStatusEnum::OPEN
                : $conversation->status,
        ]);
        $conversation->incrementUnread();

        // Broadcast new message event
        event(new NewMessageEvent($message));

        return $message;
    }

    /**
     * Get messages for a conversation.
     */
    public function getForConversation(
        Conversation|int $conversation,
        int $limit = 50,
        ?int $beforeId = null
    ): Collection {
        return $this->repository->getForConversation($conversation, $limit, $beforeId, ['sender', 'attachments']);
    }

    /**
     * Find message by ID.
     */
    public function find(int $id, array $relations = []): ?Message
    {
        return $this->repository->find($id, $relations);
    }

    /**
     * Find message by external ID.
     */
    public function findByExternalId(string $externalId): ?Message
    {
        return $this->repository->findByExternalId($externalId);
    }

    /**
     * Update message status (e.g., from webhook callback).
     */
    public function updateStatus(Message $message, MessageStatusEnum $status, array $additionalData = []): bool
    {
        return $this->repository->updateStatus($message, $status, $additionalData);
    }

    /**
     * Retry a failed message.
     */
    public function retry(Message $message): MessageResultDTO
    {
        if (! $message->canRetry()) {
            return MessageResultDTO::failure(
                message: $message,
                errorCode: 'CANNOT_RETRY',
                errorMessage: 'This message cannot be retried.'
            );
        }

        // Reset status to pending
        $message->update(['status' => MessageStatusEnum::PENDING]);

        // Rebuild DTO and send
        $dto = SendMessageDTO::make(
            conversation: $message->conversation,
            content: $message->content,
            messageType: $message->message_type,
            senderId: $message->sender_id,
            mediaUrl: $message->media_url,
            metadata: $message->metadata ?? []
        );

        $channel = ChannelFactory::make($message->conversation->channel);

        return $channel->sendMessage($dto);
    }

    /**
     * Get failed messages for retry.
     */
    public function getFailedMessages(int $limit = 100): Collection
    {
        return $this->repository->getFailedMessages($limit);
    }

    /**
     * Mark messages as read.
     */
    public function markAsRead(Conversation|int $conversation): int
    {
        return $this->repository->markAsRead($conversation);
    }

    /**
     * Delete a message (soft delete).
     */
    public function delete(Message $message): bool
    {
        return $this->repository->delete($message);
    }
}
