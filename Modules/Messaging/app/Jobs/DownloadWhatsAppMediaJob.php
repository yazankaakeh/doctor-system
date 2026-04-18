<?php

namespace Modules\Messaging\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Messaging\Models\Message;
use Modules\Messaging\Services\WhatsAppMediaService;

class DownloadWhatsAppMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        protected int $messageId
    ) {
        $this->onQueue('media');
    }

    public function handle(WhatsAppMediaService $mediaService): void
    {
        $message = Message::find($this->messageId);

        if (! $message) {
            Log::channel('messaging')->warning('DownloadWhatsAppMediaJob: Message not found', [
                'message_id' => $this->messageId,
            ]);

            return;
        }

        $success = $mediaService->processMessageMedia($message);

        if ($success) {
            Log::channel('messaging')->info('WhatsApp media downloaded successfully', [
                'message_id' => $this->messageId,
            ]);
        } else {
            Log::channel('messaging')->warning('Failed to download WhatsApp media', [
                'message_id' => $this->messageId,
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('messaging')->error('DownloadWhatsAppMediaJob failed permanently', [
            'message_id' => $this->messageId,
            'error' => $exception->getMessage(),
        ]);
    }
}
