<?php

namespace Modules\Messaging\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\TemplateStatusEnum;
use Modules\Messaging\Models\Channel;
use Modules\Messaging\Models\Template;

class WhatsAppTemplateSyncService
{
    protected const API_BASE_URL = 'https://graph.facebook.com';

    protected Channel $channel;

    protected string $accessToken;

    protected string $businessAccountId;

    protected string $apiVersion;

    public function __construct()
    {
        $this->channel = Channel::where('type', ChannelTypeEnum::WHATSAPP)
            ->where('is_active', true)
            ->first();

        if ($this->channel) {
            $this->accessToken = $this->channel->getConfigValue('access_token');
            $this->businessAccountId = $this->channel->getConfigValue('business_account_id');
            $this->apiVersion = $this->channel->getConfigValue('api_version', 'v18.0');
        }
    }

    /**
     * Check if the service is properly configured.
     */
    public function isConfigured(): bool
    {
        return $this->channel
            && ! empty($this->accessToken)
            && ! empty($this->businessAccountId);
    }

    /**
     * Sync all templates from WhatsApp Business API.
     */
    public function syncAll(): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'WhatsApp channel not configured. Missing access_token or business_account_id.',
                'synced' => 0,
                'created' => 0,
                'updated' => 0,
            ];
        }

        $templates = $this->fetchTemplatesFromApi();

        if ($templates === null) {
            return [
                'success' => false,
                'message' => 'Failed to fetch templates from WhatsApp API.',
                'synced' => 0,
                'created' => 0,
                'updated' => 0,
            ];
        }

        $created = 0;
        $updated = 0;

        foreach ($templates as $templateData) {
            $result = $this->syncTemplate($templateData);
            if ($result === 'created') {
                $created++;
            } elseif ($result === 'updated') {
                $updated++;
            }
        }

        Log::channel('messaging')->info('WhatsApp templates synced', [
            'total' => count($templates),
            'created' => $created,
            'updated' => $updated,
        ]);

        return [
            'success' => true,
            'message' => "Synced {$created} new templates, updated {$updated} existing.",
            'synced' => count($templates),
            'created' => $created,
            'updated' => $updated,
        ];
    }

    /**
     * Fetch templates from WhatsApp Business API.
     */
    protected function fetchTemplatesFromApi(): ?array
    {
        try {
            $url = self::API_BASE_URL."/{$this->apiVersion}/{$this->businessAccountId}/message_templates";

            $response = Http::withToken($this->accessToken)
                ->timeout(30)
                ->get($url, [
                    'limit' => 250, // Max templates per request
                ]);

            if (! $response->successful()) {
                Log::channel('messaging')->error('Failed to fetch WhatsApp templates', [
                    'status' => $response->status(),
                    'error' => $response->json('error'),
                ]);

                return null;
            }

            $templates = $response->json('data', []);

            // Handle pagination
            $paging = $response->json('paging');
            while (isset($paging['next'])) {
                $nextResponse = Http::withToken($this->accessToken)
                    ->timeout(30)
                    ->get($paging['next']);

                if ($nextResponse->successful()) {
                    $templates = array_merge($templates, $nextResponse->json('data', []));
                    $paging = $nextResponse->json('paging');
                } else {
                    break;
                }
            }

            return $templates;
        } catch (\Exception $e) {
            Log::channel('messaging')->error('Exception fetching WhatsApp templates', [
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Sync a single template from API data.
     */
    protected function syncTemplate(array $data): string
    {
        $externalId = $data['id'];
        $name = $data['name'];
        $language = $data['language'];
        $status = $this->mapStatus($data['status']);
        $category = $data['category'] ?? 'UTILITY';

        // Parse components
        $components = $data['components'] ?? [];
        $headerType = null;
        $headerContent = null;
        $body = '';
        $footer = null;
        $buttons = [];

        foreach ($components as $component) {
            switch ($component['type']) {
                case 'HEADER':
                    $headerType = strtolower($component['format'] ?? 'text');
                    if ($headerType === 'text') {
                        $headerContent = $component['text'] ?? '';
                    }
                    break;

                case 'BODY':
                    $body = $component['text'] ?? '';
                    break;

                case 'FOOTER':
                    $footer = $component['text'] ?? '';
                    break;

                case 'BUTTONS':
                    $buttons = $component['buttons'] ?? [];
                    break;
            }
        }

        // Find existing or create new
        $template = Template::where('channel_id', $this->channel->id)
            ->where('external_template_id', $externalId)
            ->first();

        $templateData = [
            'channel_id' => $this->channel->id,
            'name' => $name,
            'external_template_id' => $externalId,
            'language' => $language,
            'category' => $category,
            'header_type' => $headerType,
            'header_content' => $headerContent,
            'body' => $body,
            'footer' => $footer,
            'buttons' => $buttons,
            'parameter_format' => '{{1}}', // WhatsApp uses {{1}}, {{2}}, etc.
            'status' => $status,
            'is_active' => $status === TemplateStatusEnum::APPROVED,
        ];

        if ($template) {
            $template->update($templateData);

            return 'updated';
        }

        Template::create($templateData);

        return 'created';
    }

    /**
     * Map WhatsApp API status to our enum.
     */
    protected function mapStatus(string $status): TemplateStatusEnum
    {
        return match (strtoupper($status)) {
            'APPROVED' => TemplateStatusEnum::APPROVED,
            'PENDING' => TemplateStatusEnum::PENDING,
            'REJECTED' => TemplateStatusEnum::REJECTED,
            'DISABLED' => TemplateStatusEnum::DISABLED,
            'PAUSED' => TemplateStatusEnum::PAUSED,
            default => TemplateStatusEnum::PENDING,
        };
    }

    /**
     * Sync a specific template by name.
     */
    public function syncByName(string $name, string $language = 'en'): ?Template
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $url = self::API_BASE_URL."/{$this->apiVersion}/{$this->businessAccountId}/message_templates";

        $response = Http::withToken($this->accessToken)
            ->timeout(30)
            ->get($url, [
                'name' => $name,
                'language' => $language,
            ]);

        if (! $response->successful()) {
            return null;
        }

        $templates = $response->json('data', []);

        if (empty($templates)) {
            return null;
        }

        $this->syncTemplate($templates[0]);

        return Template::where('channel_id', $this->channel->id)
            ->where('name', $name)
            ->where('language', $language)
            ->first();
    }

    /**
     * Delete templates that no longer exist in WhatsApp.
     */
    public function cleanupOrphaned(): int
    {
        if (! $this->isConfigured()) {
            return 0;
        }

        $apiTemplates = $this->fetchTemplatesFromApi();

        if ($apiTemplates === null) {
            return 0;
        }

        $apiExternalIds = collect($apiTemplates)->pluck('id')->toArray();

        // Find local templates not in API
        $orphaned = Template::where('channel_id', $this->channel->id)
            ->whereNotNull('external_template_id')
            ->whereNotIn('external_template_id', $apiExternalIds)
            ->get();

        $count = $orphaned->count();

        // Mark as disabled instead of deleting
        foreach ($orphaned as $template) {
            $template->update([
                'status' => TemplateStatusEnum::DISABLED,
                'is_active' => false,
            ]);
        }

        return $count;
    }
}
