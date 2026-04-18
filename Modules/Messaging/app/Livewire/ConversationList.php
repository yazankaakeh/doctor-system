<?php

namespace Modules\Messaging\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\ConversationStatusEnum;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Services\MessagingService;

class ConversationList extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $channelFilter = null;

    public ?string $statusFilter = null;

    public ?int $assignedFilter = null;

    public ?int $selectedConversationId = null;

    public int $perPage = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'channelFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedChannelFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function selectConversation(int $conversationId): void
    {
        $this->selectedConversationId = $conversationId;
        $this->dispatch('conversation-selected', conversationId: $conversationId);

        // Mark as read
        $conversation = Conversation::find($conversationId);
        if ($conversation) {
            $service = app(MessagingService::class);
            $service->markAsRead($conversation);
        }
    }

    #[On('echo-private:agent.{userId},new-message')]
    public function onNewMessage(): void
    {
        // Refresh the list when new message arrives
    }

    #[On('conversation-updated')]
    public function onConversationUpdated(): void
    {
        // Refresh the list when conversation is updated
    }

    public function getConversationsProperty()
    {
        $query = Conversation::query()
            ->with(['channel', 'assignedUser', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->withCount(['messages as unread_count' => function ($q) {
                $q->where('direction', 'inbound')->whereNull('read_at');
            }]);

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('participant_name', 'like', "%{$this->search}%")
                    ->orWhere('participant_identifier', 'like', "%{$this->search}%");
            });
        }

        // Apply channel filter
        if ($this->channelFilter) {
            $query->whereHas('channel', fn ($q) => $q->where('type', $this->channelFilter));
        }

        // Apply status filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Apply assignment filter
        if (auth()->user()->isAdmin()) {
            if ($this->assignedFilter) {
                $query->where('assigned_user_id', $this->assignedFilter);
            }
        } else {
            $query->where('assigned_user_id', auth()->id());
        }

        return $query
            ->orderByDesc('last_message_at')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('messaging::livewire.conversation-list', [
            'conversations' => $this->conversations,
            'channelOptions' => collect(ChannelTypeEnum::cases())
                ->mapWithKeys(fn ($c) => [$c->value => $c->label()])
                ->toArray(),
            'statusOptions' => collect(ConversationStatusEnum::cases())
                ->mapWithKeys(fn ($s) => [$s->value => $s->label()])
                ->toArray(),
        ]);
    }
}
