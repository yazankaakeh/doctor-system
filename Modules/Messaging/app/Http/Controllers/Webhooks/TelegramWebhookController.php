<?php

namespace Modules\Messaging\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Jobs\ProcessWebhookJob;
use Modules\Messaging\Models\Channel;
use Modules\Messaging\Models\WebhookLog;

class TelegramWebhookController extends Controller
{
    /**
     * Handle incoming webhook from Telegram.
     */
    public function handle(Request $request): Response
    {
        $channel = Channel::where('type', ChannelTypeEnum::TELEGRAM)->first();

        if (! $channel) {
            Log::channel('messaging')->error('Telegram webhook: Channel not found');

            return response('Channel not found', 404);
        }

        // Verify secret token if configured
        $secretToken = $channel->getConfigValue('webhook_secret');
        if ($secretToken) {
            $headerToken = $request->header('X-Telegram-Bot-Api-Secret-Token');

            if ($headerToken !== $secretToken) {
                Log::channel('messaging')->warning('Telegram webhook: Invalid secret token');

                return response('Invalid token', 401);
            }
        }

        // Detect event type
        $eventType = $this->detectEventType($request->all());

        // Log the webhook
        $webhookLog = WebhookLog::create([
            'channel_id' => $channel->id,
            'event_type' => $eventType,
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
            'processed' => false,
        ]);

        // Dispatch job to process webhook
        ProcessWebhookJob::dispatch($webhookLog->id);

        Log::channel('messaging')->info('Telegram webhook received', [
            'webhook_log_id' => $webhookLog->id,
            'event_type' => $eventType,
        ]);

        return response('OK', 200);
    }

    /**
     * Detect the type of webhook event.
     */
    protected function detectEventType(array $payload): string
    {
        if (isset($payload['message'])) {
            return 'message';
        }

        if (isset($payload['edited_message'])) {
            return 'edited_message';
        }

        if (isset($payload['callback_query'])) {
            return 'callback_query';
        }

        if (isset($payload['inline_query'])) {
            return 'inline_query';
        }

        return 'unknown';
    }
}
