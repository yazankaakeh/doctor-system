<?php

namespace Modules\Messaging\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Messaging\Channels\ChannelFactory;
use Modules\Messaging\DataTransferObjects\SendMessageDTO;
use Modules\Messaging\Enums\MessageStatusEnum;
use Modules\Messaging\Models\Message;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    protected int $messageId;

    protected array $dtoData;

    public function __construct(int $messageId, array $dtoData)
    {
        $this->messageId = $messageId;
        $this->dtoData = $dtoData;
        $this->onQueue('messaging');
    }

    public function handle(): void
    {
        $message = Message::with(['conversation.channel'])->find($this->messageId);

        if (! $message) {
            Log::channel('messaging')->warning('SendMessageJob: Message not found', [
                'message_id' => $this->messageId,
            ]);

            return;
        }

        // Skip if already processed
        if ($message->status !== MessageStatusEnum::QUEUED && $message->status !== MessageStatusEnum::PENDING) {
            return;
        }

        try {
            // Get the channel
            $channel = ChannelFactory::make($message->conversation->channel);

            // Rebuild DTO
            $dto = SendMessageDTO::make(
                conversation: $message->conversation,
                content: $message->content,
                messageType: $message->message_type,
                senderId: $message->sender_id,
                mediaUrl: $message->media_url,
                metadata: $message->metadata ?? []
            );

            // Send the message
            $result = $channel->sendMessage($dto);

            if ($result->isFailure()) {
                Log::channel('messaging')->error('SendMessageJob: Send failed', [
                    'message_id' => $this->messageId,
                    'error' => $result->getError(),
                ]);

                // Update message with error
                $message->update([
                    'status' => MessageStatusEnum::FAILED,
                    'error_code' => $result->errorCode,
                    'error_message' => $result->errorMessage,
                ]);
            }
        } catch (\Exception $e) {
            Log::channel('messaging')->error('SendMessageJob: Exception', [
                'message_id' => $this->messageId,
                'exception' => $e->getMessage(),
            ]);

            $message->update([
                'status' => MessageStatusEnum::FAILED,
                'error_code' => 'EXCEPTION',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('messaging')->error('SendMessageJob: Job failed permanently', [
            'message_id' => $this->messageId,
            'exception' => $exception->getMessage(),
        ]);

        $message = Message::find($this->messageId);
        if ($message) {
            $message->update([
                'status' => MessageStatusEnum::FAILED,
                'error_code' => 'JOB_FAILED',
                'error_message' => 'Job failed after max retries: '.$exception->getMessage(),
            ]);
        }
    }
}
