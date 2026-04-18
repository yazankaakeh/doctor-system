<?php

namespace Modules\Messaging\Livewire;

use App\Models\User;
use Exception;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\ConversationStatusEnum;
use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Enums\MessageStatusEnum;
use Modules\Messaging\Enums\MessageTypeEnum;
use Modules\Messaging\Enums\SenderTypeEnum;
use Modules\Messaging\Models\Channel;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;
use Modules\Messaging\Services\MessagingService;

class GlobalMessagingPanel extends Component
{
    public bool $isOpen = false;

    public ?int $selectedConversationId = null;

    public string $searchQuery = '';

    public ?string $channelFilter = null;

    public ?string $statusFilter = null;

    public $conversations = [];

    // New conversation properties
    public bool $showNewConversation = false;

    public ?string $newConversationChannel = null;

    public string $recipientIdentifier = '';

    public string $recipientName = '';

    public string $initialMessage = '';

    public bool $isSending = false;

    // User search properties
    public string $userSearch = '';

    public ?int $selectedUserId = null;

    public $searchedUsers = [];

    protected $listeners = [
        'toggle-messaging-panel' => 'toggle',
        'open-messaging-panel' => 'open',
        'close-messaging-panel' => 'close',
        'conversation-selected' => 'selectConversation',
    ];

    protected $rules = [
        'newConversationChannel' => 'required',
        'recipientIdentifier' => 'required|string|min:5',
        'initialMessage' => 'required|string|min:1',
    ];

    public function mount(): void
    {
        $this->loadConversations();
    }

    public function loadConversations(): void
    {
        if (! auth()->check()) {
            return;
        }

        $user = auth()->user();

        $query = Conversation::with(['channel', 'assignedUser', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->where(function ($q) use ($user) {
                // Conversations assigned to current user (as agent/sender)
                $q->where('assigned_user_id', $user->id);

                // OR conversations where current user is the participant (recipient)
                if ($user->full_mobile) {
                    $q->orWhere('participant_identifier', $user->full_mobile);
                }
                if ($user->email) {
                    $q->orWhere('participant_identifier', $user->email);
                }
                // Also check for user:ID format
                $q->orWhere('participant_identifier', "user:{$user->id}");
            })
            ->orderByDesc('last_message_at');

        if ($this->channelFilter) {
            $channelType = ChannelTypeEnum::tryFrom($this->channelFilter);
            if ($channelType) {
                $query->whereHas('channel', fn ($q) => $q->where('type', $channelType));
            }
        }

        if ($this->statusFilter) {
            $status = ConversationStatusEnum::tryFrom($this->statusFilter);
            if ($status) {
                $query->where('status', $status);
            }
        }

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('participant_name', 'like', '%'.$this->searchQuery.'%')
                    ->orWhere('participant_identifier', 'like', '%'.$this->searchQuery.'%')
                    ->orWhereHas('messages', fn ($mq) => $mq->where('content', 'like', '%'.$this->searchQuery.'%'));
            });
        }

        $this->conversations = $query->get();
    }

    public function toggle(): void
    {
        $this->isOpen = ! $this->isOpen;

        if ($this->isOpen) {
            $this->loadConversations();
        }
    }

    public function open(): void
    {
        $this->isOpen = true;
        $this->loadConversations();
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->selectedConversationId = null;
        $this->showNewConversation = false;
        $this->resetNewConversationForm();
    }

    public function resetNewConversationForm(): void
    {
        $this->newConversationChannel = null;
        $this->recipientIdentifier = '';
        $this->recipientName = '';
        $this->initialMessage = '';
        $this->isSending = false;
        $this->userSearch = '';
        $this->selectedUserId = null;
        $this->searchedUsers = [];
    }

    public function setChannelFilter(?string $channel): void
    {
        $this->channelFilter = $channel ?: null;
        $this->loadConversations();
    }

    public function getConversationCountsProperty(): array
    {
        if (! auth()->check()) {
            return ['all' => 0];
        }

        $user = auth()->user();

        $baseQuery = Conversation::query()
            ->where(function ($q) use ($user) {
                $q->where('assigned_user_id', $user->id);
                if ($user->full_mobile) {
                    $q->orWhere('participant_identifier', $user->full_mobile);
                }
                if ($user->email) {
                    $q->orWhere('participant_identifier', $user->email);
                }
                $q->orWhere('participant_identifier', "user:{$user->id}");
            })
            ->where('unread_count', '>', 0);

        $counts = ['all' => (clone $baseQuery)->count()];

        foreach (ChannelTypeEnum::cases() as $channelType) {
            $counts[$channelType->value] = (clone $baseQuery)
                ->whereHas('channel', fn ($q) => $q->where('type', $channelType))
                ->count();
        }

        return $counts;
    }

    public function updatedSearchQuery(): void
    {
        $this->loadConversations();
    }

    public function clearSearch(): void
    {
        $this->searchQuery = '';
        $this->loadConversations();
    }

    public function updatedChannelFilter(): void
    {
        $this->loadConversations();
    }

    public function updatedStatusFilter(): void
    {
        $this->loadConversations();
    }

    public function selectConversation(int $conversationId): void
    {
        $this->selectedConversationId = $conversationId;
        $this->showNewConversation = false;

        // Mark as read
        $conversation = Conversation::find($conversationId);
        if ($conversation) {
            $service = app(MessagingService::class);
            $service->markAsRead($conversation);
            $this->dispatch('messaging-read');
        }
    }

    public function backToList(): void
    {
        $this->selectedConversationId = null;
        $this->showNewConversation = false;
        $this->resetNewConversationForm();
        $this->loadConversations();
    }

    public function showNewConversationForm(): void
    {
        $this->showNewConversation = true;
        $this->selectedConversationId = null;
        $this->resetNewConversationForm();
    }

    public function setChannel(string $channel): void
    {
        $this->newConversationChannel = $channel;
        $this->clearSelectedUser();
    }

    public function clearSelectedUser(): void
    {
        $this->selectedUserId = null;
        $this->recipientIdentifier = '';
        $this->recipientName = '';
        $this->userSearch = '';
        $this->searchedUsers = [];
    }

    public function updatedUserSearch(): void
    {
        if (strlen($this->userSearch) >= 2) {
            $this->searchedUsers = User::query()
                ->with('userInfo')
                ->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->userSearch.'%')
                        ->orWhere('email', 'like', '%'.$this->userSearch.'%')
                        ->orWhereHas('userInfo', function ($q) {
                            $q->where('mobile', 'like', '%'.$this->userSearch.'%');
                        });
                })
                ->whereHas('userInfo', function ($q) {
                    $q->whereNotNull('mobile')
                        ->where('mobile', '!=', '');
                })
                ->limit(10)
                ->get();
        } else {
            $this->searchedUsers = [];
        }
    }

    public function selectUser(int $userId): void
    {
        $user = User::with('userInfo')->find($userId);
        if ($user) {
            $this->selectedUserId = $userId;
            // Use phone number as identifier, or fall back to user:ID format
            $this->recipientIdentifier = $user->full_mobile ?: "user:{$userId}";
            $this->recipientName = $user->name ?? '';
            $this->userSearch = $user->name;
            $this->searchedUsers = [];
        }
    }

    public function startConversation(): void
    {
        $this->validate();

        $this->isSending = true;

        try {
            $service = app(MessagingService::class);
            $channelType = ChannelTypeEnum::from($this->newConversationChannel);

            // Create or find conversation
            $conversation = $service->conversation(
                $this->recipientIdentifier,
                $channelType,
                null,
                $this->recipientName ?: null
            );

            // Assign to current user
            $service->assignConversation($conversation, auth()->id());

            // Send initial message (synchronously for immediate feedback)
            $result = $service->send(
                $conversation,
                $this->initialMessage,
                MessageTypeEnum::TEXT,
                auth()->id(),
                ['async' => false]
            );

            // Check if message was created (even if sending failed)
            if (! $result->isSuccess() && ! $result->message) {
                // Channel not configured or other issue - create message manually
                Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_type' => SenderTypeEnum::USER,
                    'sender_id' => auth()->id(),
                    'direction' => MessageDirectionEnum::OUTBOUND,
                    'message_type' => MessageTypeEnum::TEXT,
                    'content' => $this->initialMessage,
                    'status' => MessageStatusEnum::SENT,
                    'sent_at' => now(),
                ]);
                $conversation->updateLastMessageAt();
                $conversation->incrementUnread();
            } elseif ($result->isSuccess() || $result->message) {
                // Increment unread for recipient
                $conversation->incrementUnread();
            }

            // Select the new conversation
            $this->selectedConversationId = $conversation->id;
            $this->showNewConversation = false;
            $this->resetNewConversationForm();
            $this->loadConversations();

            $this->dispatch('messaging-new-message');

        } catch (Exception $e) {
            $this->addError('initialMessage', $e->getMessage());
        } finally {
            $this->isSending = false;
        }
    }

    #[On('messaging-new-message')]
    public function onNewMessage(): void
    {
        $this->loadConversations();
    }

    public function render()
    {
        return view('messaging::livewire.global-messaging-panel', [
            'channelOptions' => $this->getChannelOptions(),
            'statusOptions' => $this->getStatusOptions(),
            'availableChannels' => $this->getAvailableChannels(),
            'conversationCounts' => $this->conversationCounts,
        ]);
    }

    public function getChannelOptions(): array
    {
        return collect(ChannelTypeEnum::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public function getStatusOptions(): array
    {
        return collect(ConversationStatusEnum::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public function getAvailableChannels(): array
    {
        return Channel::active()
            ->get()
            ->mapWithKeys(fn ($channel) => [
                $channel->type->value => [
                    'name' => $channel->name,
                    'type' => $channel->type,
                    'icon' => $channel->type->icon(),
                    'color' => $channel->type->color(),
                    'placeholder' => $this->getPlaceholderForChannel($channel->type),
                ],
            ])
            ->toArray();
    }

    protected function getPlaceholderForChannel(ChannelTypeEnum $type): string
    {
        return match ($type) {
            ChannelTypeEnum::WHATSAPP => '+1234567890',
            ChannelTypeEnum::TELEGRAM => '@username',
            ChannelTypeEnum::WEBCHAT => 'visitor@email.com',
        };
    }
}
