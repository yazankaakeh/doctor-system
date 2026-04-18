<?php

namespace Modules\Messaging\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Models\Channel;
use Modules\Messaging\Models\Message;

class WhatsAppReadReceiptService
{
    protected ?Channel $channel = null;

    protected ?string $accessToken = null;

    protected string $apiVersion = 'v18.0';

    protected const API_BASE_URL = 'https://graph.facebook.com';

    public function __construct()
    {
        $this->channel = Channel::where('type', ChannelTypeEnum::WHATSAPP)
            ->where('is_active', true)
            ->first();

        if ($this->channel) {
            $this->accessToken = $this->channel->getConfigValue('access_token');
            $this->apiVersion = $this->channel->getConfigValue('api_version', 'v18.0');
        }
    }

    /**
     * Send read receipt to WhatsApp for a message.
     */
    public function markAsRead(Message $message): bool
    {
        if (! $this->accessToken || ! $this->channel) {
            return false;
        }

        // Only send read receipts for inbound WhatsApp messages
        if (! $message->external_id) {
            return false;
        }

        $conversation = $message->conversation;
        if (! $conversation || $conversation->channel->type !== ChannelTypeEnum::WHATSAPP) {
            return false;
        }

        try {
            $phoneNumberId = $this->channel->getConfigValue('phone_number_id');

            $response = Http::withToken($this->accessToken)
                ->timeout(30)
                ->post(self::API_BASE_URL."/{$this->apiVersion}/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'status' => 'read',
                    'message_id' => $message->external_id,
                ]);

            if ($response->successful()) {
                Log::channel('messaging')->info('Read receipt sent to WhatsApp', [
                    'message_id' => $message->id,
                    'external_id' => $message->external_id,
                ]);

                return true;
            }

            Log::channel('messaging')->warning('Failed to send read receipt', [
                'message_id' => $message->id,
                'error' => $response->json('error'),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::channel('messaging')->error('Exception sending read receipt', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Mark multiple messages as read.
     */
    public function markMultipleAsRead(array $messageIds): int
    {
        $count = 0;

        foreach ($messageIds as $messageId) {
            $message = Message::find($messageId);
            if ($message && $this->markAsRead($message)) {
                $count++;
            }
        }

        return $count;
    }
}
