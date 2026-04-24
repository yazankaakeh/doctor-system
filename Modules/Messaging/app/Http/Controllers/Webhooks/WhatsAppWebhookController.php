<?php

/**
 * -----------------------------------------------------------------------------
 * WhatsAppWebhookController
 * -----------------------------------------------------------------------------
 *
 * Handles incoming webhooks from Meta's WhatsApp Business Cloud API.
 *
 * Two routes:
 *   - GET  → verify() — Meta's handshake/verification challenge.
 *   - POST → handle() — actual events (incoming messages, status updates).
 *
 * The POST handler validates the HMAC-SHA256 signature, logs the payload
 * into WebhookLog for auditability, and queues ProcessWebhookJob so
 * processing happens async. We must respond with 200 quickly or Meta will
 * re-deliver.
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

class WhatsAppWebhookController extends Controller
{
    /**
     * Handshake endpoint. Meta sends a GET with `hub_mode`, `hub_verify_token`,
     * `hub_challenge`. If the verify token matches the one stored on the
     * Channel row, we echo the challenge back; otherwise respond 403.
     */
    public function verify(Request $request): Response
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        // Prefer the per-channel override, fall back to the env config default.
        $channel     = Channel::where('type', ChannelTypeEnum::WHATSAPP)->first();
        $verifyToken = $channel?->getConfigValue('verify_token', config('services.whatsapp.verify_token'));

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::channel('messaging')->info('WhatsApp webhook verified');

            // Meta requires us to echo the challenge string unchanged.
            return response($challenge, 200);
        }

        Log::channel('messaging')->warning('WhatsApp webhook verification failed', [
            'mode'        => $mode,
            'token_match' => $token === $verifyToken,
        ]);

        return response('Verification failed', 403);
    }

    /**
     * Event endpoint. Stores the payload, validates the signature, and
     * queues async processing.
     */
    public function handle(Request $request): Response
    {
        $channel = Channel::where('type', ChannelTypeEnum::WHATSAPP)->first();

        if (! $channel) {
            Log::channel('messaging')->error('WhatsApp webhook: Channel not found');

            return response('Channel not found', 404);
        }

        // Log BEFORE verifying signature so even rejected events are auditable.
        $webhookLog = WebhookLog::create([
            'channel_id' => $channel->id,
            'event_type' => $this->detectEventType($request->all()),
            'payload'    => $request->all(),
            'headers'    => $request->headers->all(),
            'processed'  => false,
        ]);

        // HMAC signature verification (only when an app_secret is configured).
        $signature = $request->header('X-Hub-Signature-256');
        $appSecret = $channel->getConfigValue('app_secret');

        if ($appSecret) {
            $expectedSignature = 'sha256='.hash_hmac('sha256', $request->getContent(), $appSecret);

            // Timing-safe comparison protects against timing-attack guessing.
            if (! hash_equals($expectedSignature, $signature ?? '')) {
                Log::channel('messaging')->warning('WhatsApp webhook: Invalid signature', [
                    'webhook_log_id' => $webhookLog->id,
                ]);
                $webhookLog->markAsFailed('Invalid signature');

                return response('Invalid signature', 401);
            }
        }

        // Queue the real processing so we can ack quickly.
        ProcessWebhookJob::dispatch($webhookLog->id);

        Log::channel('messaging')->info('WhatsApp webhook received', [
            'webhook_log_id' => $webhookLog->id,
            'event_type'     => $webhookLog->event_type,
        ]);

        // Meta requires us to always ack 200 once we've taken delivery.
        return response('OK', 200);
    }

    /**
     * Normalise the Meta payload into a simple event label.
     * WhatsApp webhooks use a deep `entry[0].changes[0].value` structure
     * that contains either `messages` (inbound) or `statuses` (delivery
     * receipts).
     */
    protected function detectEventType(array $payload): string
    {
        $entry   = $payload['entry'][0] ?? [];
        $changes = $entry['changes'][0] ?? [];
        $value   = $changes['value'] ?? [];

        if (! empty($value['messages'])) {
            return 'message';
        }

        if (! empty($value['statuses'])) {
            return 'status';
        }

        return 'unknown';
    }
}
