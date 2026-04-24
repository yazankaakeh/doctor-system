<?php

/**
 * -----------------------------------------------------------------------------
 * TelegramWebhookController
 * -----------------------------------------------------------------------------
 *
 * Receives webhook callbacks from the Telegram Bot API.
 *
 * Responsibilities:
 *   1. Authenticate the request (via X-Telegram-Bot-Api-Secret-Token header
 *      if a secret was configured on the Channel row).
 *   2. Persist a WebhookLog entry with the raw payload for auditability.
 *   3. Dispatch ProcessWebhookJob so the heavy lifting happens async — we
 *      return `OK` quickly so Telegram doesn't retry.
 *
 * Telegram expects a 200 within a few seconds or it will re-deliver the
 * update, so this controller must stay fast and not do actual processing
 * inline.
 * -----------------------------------------------------------------------------
 */

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
     * Entry point hit by Telegram for every "update".
     */
    public function handle(Request $request): Response
    {
        // Resolve the Telegram channel configuration row.
        $channel = Channel::where('type', ChannelTypeEnum::TELEGRAM)->first();

        if (! $channel) {
            Log::channel('messaging')->error('Telegram webhook: Channel not found');

            return response('Channel not found', 404);
        }

        // Optional secret validation: when configured, reject requests that
        // don't carry the expected header. Protects against forged webhooks.
        $secretToken = $channel->getConfigValue('webhook_secret');
        if ($secretToken) {
            $headerToken = $request->header('X-Telegram-Bot-Api-Secret-Token');

            if ($headerToken !== $secretToken) {
                Log::channel('messaging')->warning('Telegram webhook: Invalid secret token');

                return response('Invalid token', 401);
            }
        }

        // Normalize the event category for easier querying later.
        $eventType = $this->detectEventType($request->all());

        // Persist the full payload BEFORE processing so we always have a
        // record even if the job fails or the server crashes.
        $webhookLog = WebhookLog::create([
            'channel_id' => $channel->id,
            'event_type' => $eventType,
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
            'processed' => false,
        ]);

        // Offload the real work to the queue so we can ack Telegram quickly.
        ProcessWebhookJob::dispatch($webhookLog->id);

        Log::channel('messaging')->info('Telegram webhook received', [
            'webhook_log_id' => $webhookLog->id,
            'event_type' => $eventType,
        ]);

        // Telegram only cares about HTTP 200 — the body is ignored.
        return response('OK', 200);
    }

    /**
     * Map the raw Telegram update shape to a simple string event type.
     * Unknown shapes are bucketed as 'unknown' so we never throw.
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
