<?php

namespace Modules\Messaging\Livewire\Lead;

use Livewire\Component;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Services\MessagingService;

class LeadMessaging extends Component
{
    public $lead = null;

    public ?string $selectedChannel = null;

    public ?int $selectedConversationId = null;

    public $conversations = [];

    protected $listeners = [
        'lead-channel-selected' => 'selectChannel',
    ];

    public function mount($lead): void
    {
        $this->lead = $lead;
        $this->loadConversations();

        // Auto-select first available channel
        if ($this->conversations->isNotEmpty()) {
            $firstConversation = $this->conversations->first();
            $this->selectedChannel = $firstConversation->channel->type->value;
            $this->selectedConversationId = $firstConversation->id;
        }
    }

    public function loadConversations(): void
    {
        if (! $this->lead || ! method_exists($this->lead, 'conversations')) {
            $this->conversations = collect();

            return;
        }

        $this->conversations = $this->lead->conversations()
            ->with(['channel', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderBy('last_message_at', 'desc')
            ->get();
    }

    public function selectChannel(string $channelType): void
    {
        $this->selectedChannel = $channelType;

        // Find conversation for this channel
        $conversation = $this->conversations->first(
            fn ($c) => $c->channel->type->value === $channelType
        );

        $this->selectedConversationId = $conversation?->id;
    }

    public function startNewConversation(string $channelType): void
    {
        if (! $this->lead) {
            return;
        }

        $channel = ChannelTypeEnum::tryFrom($channelType);
        if (! $channel) {
            return;
        }

        // Get identifier from lead
        $identifier = null;
        if (method_exists($this->lead, 'getDefaultMessagingIdentifier')) {
            $identifier = $this->lead->getDefaultMessagingIdentifier();
        }

        if (! $identifier) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No contact identifier available for this lead.',
            ]);

            return;
        }

        // Create or get conversation
        $service = app(MessagingService::class);
        $conversation = $service->conversation(
            $identifier,
            $channel,
            $this->lead,
            $this->lead->getMessagingDisplayName() ?? 'Unknown'
        );

        $this->loadConversations();
        $this->selectedChannel = $channelType;
        $this->selectedConversationId = $conversation->id;
    }

    public function getAvailableChannels(): array
    {
        $channels = [];

        // Channels where we have existing conversations
        foreach ($this->conversations as $conversation) {
            $channels[$conversation->channel->type->value] = [
                'type' => $conversation->channel->type,
                'hasConversation' => true,
                'unreadCount' => $conversation->unread_count,
            ];
        }

        // Add channels that can be started
        foreach (ChannelTypeEnum::cases() as $channelType) {
            if (! isset($channels[$channelType->value])) {
                // Check if user can use this channel
                if ($channelType->isAdminOnly() && ! auth()->user()->isAdmin()) {
                    continue;
                }

                $channels[$channelType->value] = [
                    'type' => $channelType,
                    'hasConversation' => false,
                    'unreadCount' => 0,
                ];
            }
        }

        return $channels;
    }

    public function render()
    {
        return view('messaging::livewire.lead.lead-messaging', [
            'availableChannels' => $this->getAvailableChannels(),
        ]);
    }
}
