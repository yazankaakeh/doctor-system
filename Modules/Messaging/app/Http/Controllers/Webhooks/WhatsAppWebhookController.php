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

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle webhook verification (GET request from Meta).
     */
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $channel = Channel::where('type', ChannelTypeEnum::WHATSAPP)->first();
        $verifyToken = $channel?->getConfigValue('verify_token', config('services.whatsapp.verify_token'));

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::channel('messaging')->info('WhatsApp webhook verified');

            return response($challenge, 200);
        }

        Log::channel('messaging')->warning('WhatsApp webhook verification failed', [
            'mode' => $mode,
            'token_match' => $token === $verifyToken,
        ]);

        return response('Verification failed', 403);
    }

    /**
     * Handle incoming webhook events (POST request from Meta).
     */
    public function handle(Request $request): Response
    {
        $channel = Channel::where('type', ChannelTypeEnum::WHATSAPP)->first();

        if (! $channel) {
            Log::channel('messaging')->error('WhatsApp webhook: Channel not found');

            return response('Channel not found', 404);
        }

        // Log the webhook
        $webhookLog = WebhookLog::create([
            'channel_id' => $channel->id,
            'event_type' => $this->detectEventType($request->all()),
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
            'processed' => false,
        ]);

        // Verify signature
        $signature = $request->header('X-Hub-Signature-256');
        $appSecret = $channel->getConfigValue('app_secret');

        if ($appSecret) {
            $expectedSignature = 'sha256='.hash_hmac('sha256', $request->getContent(), $appSecret);

            if (! hash_equals($expectedSignature, $signature ?? '')) {
                Log::channel('messaging')->warning('WhatsApp webhook: Invalid signature', [
                    'webhook_log_id' => $webhookLog->id,
                ]);
                $webhookLog->markAsFailed('Invalid signature');

                return response('Invalid signature', 401);
            }
        }

        // Dispatch job to process webhook
        ProcessWebhookJob::dispatch($webhookLog->id);

        Log::channel('messaging')->info('WhatsApp webhook received', [
            'webhook_log_id' => $webhookLog->id,
            'event_type' => $webhookLog->event_type,
        ]);

        // Always return 200 to acknowledge receipt
        return response('OK', 200);
    }

    /**
     * Detect the type of webhook event.
     */
    protected function detectEventType(array $payload): string
    {
        $entry = $payload['entry'][0] ?? [];
        $changes = $entry['changes'][0] ?? [];
        $value = $changes['value'] ?? [];

        if (! empty($value['messages'])) {
            return 'message';
        }

        if (! empty($value['statuses'])) {
            return 'status';
        }

        return 'unknown';
    }
}
