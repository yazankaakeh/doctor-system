<?php

namespace Modules\Messaging\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Models\Channel;
use Modules\Messaging\Models\Conversation;

class InteractiveMessageService
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
     * Send a message with quick reply buttons.
     */
    public function sendButtonMessage(
        Conversation $conversation,
        string $bodyText,
        array $buttons,
        ?string $headerText = null,
        ?string $footerText = null
    ): ?array {
        if (! $this->accessToken || ! $this->channel) {
            return null;
        }

        // WhatsApp allows max 3 buttons
        $buttons = array_slice($buttons, 0, 3);

        $interactive = [
            'type' => 'button',
            'body' => [
                'text' => $bodyText,
            ],
            'action' => [
                'buttons' => array_map(function ($button, $index) {
                    return [
                        'type' => 'reply',
                        'reply' => [
                            'id' => $button['id'] ?? 'btn_'.$index,
                            'title' => substr($button['title'], 0, 20), // Max 20 chars
                        ],
                    ];
                }, $buttons, array_keys($buttons)),
            ],
        ];

        if ($headerText) {
            $interactive['header'] = [
                'type' => 'text',
                'text' => $headerText,
            ];
        }

        if ($footerText) {
            $interactive['footer'] = [
                'text' => $footerText,
            ];
        }

        return $this->sendInteractiveMessage($conversation, $interactive);
    }

    /**
     * Send a list message.
     */
    public function sendListMessage(
        Conversation $conversation,
        string $bodyText,
        string $buttonText,
        array $sections,
        ?string $headerText = null,
        ?string $footerText = null
    ): ?array {
        if (! $this->accessToken || ! $this->channel) {
            return null;
        }

        $interactive = [
            'type' => 'list',
            'body' => [
                'text' => $bodyText,
            ],
            'action' => [
                'button' => substr($buttonText, 0, 20), // Max 20 chars
                'sections' => array_map(function ($section) {
                    return [
                        'title' => substr($section['title'], 0, 24), // Max 24 chars
                        'rows' => array_map(function ($row) {
                            return [
                                'id' => $row['id'],
                                'title' => substr($row['title'], 0, 24), // Max 24 chars
                                'description' => isset($row['description'])
                                    ? substr($row['description'], 0, 72) // Max 72 chars
                                    : null,
                            ];
                        }, $section['rows']),
                    ];
                }, $sections),
            ],
        ];

        if ($headerText) {
            $interactive['header'] = [
                'type' => 'text',
                'text' => $headerText,
            ];
        }

        if ($footerText) {
            $interactive['footer'] = [
                'text' => $footerText,
            ];
        }

        return $this->sendInteractiveMessage($conversation, $interactive);
    }

    /**
     * Send a CTA URL button message.
     */
    public function sendCtaUrlMessage(
        Conversation $conversation,
        string $bodyText,
        string $buttonText,
        string $url,
        ?string $headerText = null,
        ?string $footerText = null
    ): ?array {
        if (! $this->accessToken || ! $this->channel) {
            return null;
        }

        $interactive = [
            'type' => 'cta_url',
            'body' => [
                'text' => $bodyText,
            ],
            'action' => [
                'name' => 'cta_url',
                'parameters' => [
                    'display_text' => substr($buttonText, 0, 20),
                    'url' => $url,
                ],
            ],
        ];

        if ($headerText) {
            $interactive['header'] = [
                'type' => 'text',
                'text' => $headerText,
            ];
        }

        if ($footerText) {
            $interactive['footer'] = [
                'text' => $footerText,
            ];
        }

        return $this->sendInteractiveMessage($conversation, $interactive);
    }

    /**
     * Send interactive message to WhatsApp.
     */
    protected function sendInteractiveMessage(Conversation $conversation, array $interactive): ?array
    {
        try {
            $phoneNumberId = $this->channel->getConfigValue('phone_number_id');

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $conversation->participant_identifier,
                'type' => 'interactive',
                'interactive' => $interactive,
            ];

            $response = Http::withToken($this->accessToken)
                ->timeout(30)
                ->post(self::API_BASE_URL."/{$this->apiVersion}/{$phoneNumberId}/messages", $payload);

            if ($response->successful()) {
                Log::channel('messaging')->info('Interactive message sent', [
                    'conversation_id' => $conversation->id,
                    'type' => $interactive['type'],
                    'message_id' => $response->json('messages.0.id'),
                ]);

                return $response->json();
            }

            Log::channel('messaging')->error('Failed to send interactive message', [
                'conversation_id' => $conversation->id,
                'error' => $response->json('error'),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::channel('messaging')->error('Exception sending interactive message', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Process interactive message reply from webhook.
     */
    public function processInteractiveReply(array $message): array
    {
        $interactive = $message['interactive'] ?? [];
        $type = $interactive['type'] ?? null;

        return match ($type) {
            'button_reply' => [
                'type' => 'button',
                'id' => $interactive['button_reply']['id'] ?? null,
                'title' => $interactive['button_reply']['title'] ?? null,
            ],
            'list_reply' => [
                'type' => 'list',
                'id' => $interactive['list_reply']['id'] ?? null,
                'title' => $interactive['list_reply']['title'] ?? null,
                'description' => $interactive['list_reply']['description'] ?? null,
            ],
            default => [
                'type' => 'unknown',
                'raw' => $interactive,
            ],
        };
    }

    /**
     * Build a menu for common actions.
     */
    public function buildMenuList(array $menuItems): array
    {
        $sections = [];
        $currentSection = null;

        foreach ($menuItems as $item) {
            if (isset($item['section'])) {
                $currentSection = $item['section'];
                if (! isset($sections[$currentSection])) {
                    $sections[$currentSection] = [
                        'title' => $currentSection,
                        'rows' => [],
                    ];
                }
            }

            if (isset($item['id']) && isset($item['title'])) {
                $sectionKey = $currentSection ?? 'Options';
                if (! isset($sections[$sectionKey])) {
                    $sections[$sectionKey] = [
                        'title' => $sectionKey,
                        'rows' => [],
                    ];
                }

                $sections[$sectionKey]['rows'][] = [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                ];
            }
        }

        return array_values($sections);
    }
}
