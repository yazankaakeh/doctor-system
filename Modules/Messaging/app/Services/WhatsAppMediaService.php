<?php

namespace Modules\Messaging\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Models\Channel;
use Modules\Messaging\Models\Message;

class WhatsAppMediaService
{
    protected const API_BASE_URL = 'https://graph.facebook.com';

    protected ?Channel $channel = null;

    protected ?string $accessToken = null;

    protected string $apiVersion = 'v18.0';

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
     * Download media from WhatsApp and store locally.
     */
    public function downloadAndStore(string $mediaId, ?string $mimeType = null): ?array
    {
        if (! $this->accessToken) {
            Log::channel('messaging')->error('WhatsApp not configured for media download');
            return null;
        }

        try {
            // Step 1: Get media URL from WhatsApp
            $mediaInfo = $this->getMediaInfo($mediaId);

            if (! $mediaInfo) {
                return null;
            }

            // Step 2: Download the file
            $fileContent = $this->downloadFile($mediaInfo['url']);

            if (! $fileContent) {
                return null;
            }

            // Step 3: Store locally
            $mimeType = $mimeType ?? $mediaInfo['mime_type'];
            $extension = $this->getExtensionFromMime($mimeType);
            $filename = Str::uuid() . '.' . $extension;
            $path = 'messaging/media/' . date('Y/m') . '/' . $filename;

            Storage::disk('public')->put($path, $fileContent);

            return [
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
                'mime_type' => $mimeType,
                'size' => strlen($fileContent),
                'original_media_id' => $mediaId,
            ];
        } catch (\Exception $e) {
            Log::channel('messaging')->error('Failed to download WhatsApp media', [
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Download media for a message and update the message record.
     */
    public function processMessageMedia(Message $message): bool
    {
        // Check if media_url contains a media ID (not a URL)
        $mediaId = $message->media_url;

        if (! $mediaId || Str::startsWith($mediaId, ['http://', 'https://'])) {
            return false; // Already a URL or no media
        }

        $mimeType = $message->metadata['mime_type'] ?? null;
        $result = $this->downloadAndStore($mediaId, $mimeType);

        if (! $result) {
            return false;
        }

        // Update message with local URL
        $message->update([
            'media_url' => $result['url'],
            'metadata' => array_merge($message->metadata ?? [], [
                'original_media_id' => $mediaId,
                'local_path' => $result['path'],
                'file_size' => $result['size'],
            ]),
        ]);

        return true;
    }

    /**
     * Get media info from WhatsApp API.
     */
    protected function getMediaInfo(string $mediaId): ?array
    {
        $response = Http::withToken($this->accessToken)
            ->timeout(30)
            ->get(self::API_BASE_URL . "/{$this->apiVersion}/{$mediaId}");

        if (! $response->successful()) {
            Log::channel('messaging')->error('Failed to get WhatsApp media info', [
                'media_id' => $mediaId,
                'status' => $response->status(),
                'error' => $response->json('error'),
            ]);
            return null;
        }

        return [
            'url' => $response->json('url'),
            'mime_type' => $response->json('mime_type'),
            'sha256' => $response->json('sha256'),
            'file_size' => $response->json('file_size'),
        ];
    }

    /**
     * Download file from WhatsApp CDN.
     */
    protected function downloadFile(string $url): ?string
    {
        $response = Http::withToken($this->accessToken)
            ->timeout(60)
            ->get($url);

        if (! $response->successful()) {
            Log::channel('messaging')->error('Failed to download WhatsApp media file', [
                'url' => $url,
                'status' => $response->status(),
            ]);
            return null;
        }

        return $response->body();
    }

    /**
     * Get file extension from MIME type.
     */
    protected function getExtensionFromMime(string $mimeType): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'video/mp4' => 'mp4',
            'video/3gpp' => '3gp',
            'audio/aac' => 'aac',
            'audio/mp4' => 'm4a',
            'audio/mpeg' => 'mp3',
            'audio/amr' => 'amr',
            'audio/ogg' => 'ogg',
            'application/pdf' => 'pdf',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'text/plain' => 'txt',
        ];

        return $map[$mimeType] ?? 'bin';
    }

    /**
     * Upload media to WhatsApp for sending.
     */
    public function uploadMedia(string $filePath, string $mimeType): ?string
    {
        if (! $this->accessToken || ! $this->channel) {
            return null;
        }

        $phoneNumberId = $this->channel->getConfigValue('phone_number_id');

        try {
            $response = Http::withToken($this->accessToken)
                ->timeout(60)
                ->attach('file', file_get_contents($filePath), basename($filePath))
                ->post(self::API_BASE_URL . "/{$this->apiVersion}/{$phoneNumberId}/media", [
                    'messaging_product' => 'whatsapp',
                    'type' => $mimeType,
                ]);

            if ($response->successful()) {
                return $response->json('id');
            }

            Log::channel('messaging')->error('Failed to upload WhatsApp media', [
                'error' => $response->json('error'),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::channel('messaging')->error('Exception uploading WhatsApp media', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
