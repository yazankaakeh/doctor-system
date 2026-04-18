<?php

namespace Modules\Messaging\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Messaging\Channels\ChannelFactory;
use Modules\Messaging\DataTransferObjects\WebhookPayloadDTO;
use Modules\Messaging\Models\WebhookLog;
use Modules\Messaging\Services\AutoAssignmentService;
use Modules\Messaging\Services\ChatbotService;
use Modules\Messaging\Services\ContactLinkingService;
use Modules\Messaging\Services\MessageService;
use Modules\Messaging\Services\WebhookDeduplicationService;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    protected int $webhookLogId;

    public function __construct(int $webhookLogId)
    {
        $this->webhookLogId = $webhookLogId;
        $this->onQueue('webhooks');
    }

    public function handle(
        MessageService $messageService,
        WebhookDeduplicationService $deduplicationService,
        ContactLinkingService $contactLinkingService,
        AutoAssignmentService $autoAssignmentService,
        ChatbotService $chatbotService
    ): void {
        $webhookLog = WebhookLog::with('channel')->find($this->webhookLogId);

        if (! $webhookLog) {
            Log::channel('messaging')->warning('ProcessWebhookJob: WebhookLog not found', [
                'webhook_log_id' => $this->webhookLogId,
            ]);

            return;
        }

        // Skip if already processed
        if ($webhookLog->processed) {
            return;
        }

        try {
            $channelType = $webhookLog->channel?->type;

            if (! $channelType) {
                $webhookLog->markAsFailed('Channel not found or invalid');

                return;
            }

            // Check for duplicate webhook
            if (config('messaging.deduplication.enabled', true)) {
                $webhookId = $deduplicationService->generateWebhookId(
                    $webhookLog->payload,
                    $channelType->value
                );

                if (! $deduplicationService->checkAndMark($webhookId, $channelType->value)) {
                    Log::channel('messaging')->info('ProcessWebhookJob: Duplicate webhook skipped', [
                        'webhook_log_id' => $this->webhookLogId,
                        'webhook_id' => $webhookId,
                    ]);
                    $webhookLog->markAsProcessed();

                    return;
                }
            }

            // Get the channel implementation
            $channel = ChannelFactory::makeFromType($channelType);

            // Create webhook payload DTO
            $payload = WebhookPayloadDTO::make(
                channelType: $channelType,
                payload: $webhookLog->payload,
                headers: $webhookLog->headers ?? []
            );

            // Process the webhook
            $inboundDto = $channel->processWebhook($payload);

            if ($inboundDto) {
                // Process the inbound message
                $message = $messageService->processInbound($inboundDto);
                $conversation = $message->conversation;

                // Auto-link to contact/lead
                if (config('messaging.contact_linking.enabled', true)) {
                    $contactLinkingService->autoLink($conversation);
                }

                // Auto-assign agent if enabled
                if (config('messaging.assignment.enabled', true) && ! $conversation->assigned_to) {
                    $autoAssignmentService->assign($conversation);
                }

                // Process through chatbot if enabled and no agent assigned
                if ($chatbotService->isEnabled() && $chatbotService->shouldHandle($conversation)) {
                    $response = $chatbotService->processMessage($message);
                    if ($response) {
                        $chatbotService->sendResponse($conversation, $response);
                    }
                }

                // Dispatch media download job if message has media
                if ($message->media_url && $channelType === \Modules\Messaging\Enums\ChannelTypeEnum::WHATSAPP) {
                    DownloadWhatsAppMediaJob::dispatch($message->id);
                }

                Log::channel('messaging')->info('ProcessWebhookJob: Message processed', [
                    'webhook_log_id' => $this->webhookLogId,
                    'message_id' => $message->id,
                    'channel' => $channelType->value,
                    'contact_linked' => $conversation->conversable_id ? true : false,
                    'agent_assigned' => $conversation->assigned_to ? true : false,
                ]);
            }

            $webhookLog->markAsProcessed();
        } catch (\Exception $e) {
            Log::channel('messaging')->error('ProcessWebhookJob: Exception', [
                'webhook_log_id' => $this->webhookLogId,
                'exception' => $e->getMessage(),
            ]);

            $webhookLog->markAsFailed($e->getMessage());

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('messaging')->error('ProcessWebhookJob: Job failed permanently', [
            'webhook_log_id' => $this->webhookLogId,
            'exception' => $exception->getMessage(),
        ]);

        $webhookLog = WebhookLog::find($this->webhookLogId);
        if ($webhookLog) {
            $webhookLog->markAsFailed('Job failed after max retries: '.$exception->getMessage());
        }
    }
}
