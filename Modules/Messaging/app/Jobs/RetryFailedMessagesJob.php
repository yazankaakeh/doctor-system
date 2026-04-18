<?php

namespace Modules\Messaging\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Enums\MessageStatusEnum;
use Modules\Messaging\Models\Message;
use Modules\Messaging\Services\MessageService;

class RetryFailedMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $maxRetries;

    protected int $retryAfterMinutes;

    public function __construct(int $maxRetries = 3, int $retryAfterMinutes = 30)
    {
        $this->maxRetries = $maxRetries;
        $this->retryAfterMinutes = $retryAfterMinutes;
        $this->onQueue('messaging');
    }

    public function handle(MessageService $messageService): void
    {
        // Get failed messages that should be retried
        $messages = Message::query()
            ->where('status', MessageStatusEnum::FAILED)
            ->where('direction', MessageDirectionEnum::OUTBOUND)
            ->where('updated_at', '<', now()->subMinutes($this->retryAfterMinutes))
            ->whereRaw("JSON_EXTRACT(metadata, '$.retry_count') < ? OR JSON_EXTRACT(metadata, '$.retry_count') IS NULL", [$this->maxRetries])
            ->limit(100)
            ->get();

        foreach ($messages as $message) {
            try {
                // Update retry count
                $metadata = $message->metadata ?? [];
                $metadata['retry_count'] = ($metadata['retry_count'] ?? 0) + 1;
                $metadata['last_retry_at'] = now()->toIso8601String();
                $message->metadata = $metadata;
                $message->save();

                // Retry the message
                $result = $messageService->retry($message);

                Log::channel('messaging')->info('RetryFailedMessagesJob: Retry attempted', [
                    'message_id' => $message->id,
                    'retry_count' => $metadata['retry_count'],
                    'success' => $result->isSuccess(),
                ]);
            } catch (\Exception $e) {
                Log::channel('messaging')->error('RetryFailedMessagesJob: Retry exception', [
                    'message_id' => $message->id,
                    'exception' => $e->getMessage(),
                ]);
            }
        }
    }
}
