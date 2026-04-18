<?php

namespace Modules\Messaging\Livewire;

use Livewire\Component;
use Modules\Messaging\Models\Channel;
use Modules\Messaging\Models\Conversation;

class AgentDashboard extends Component
{
    public ?int $selectedConversationId = null;

    public array $stats = [];

    protected $listeners = [
        'conversation-selected' => 'selectConversation',
    ];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $userId = auth()->id();

        $this->stats = [
            'total_conversations' => Conversation::where('assigned_user_id', $userId)->count(),
            'open_conversations' => Conversation::where('assigned_user_id', $userId)->active()->count(),
            'unread_count' => Conversation::where('assigned_user_id', $userId)->sum('unread_count'),
            'unassigned' => auth()->user()->isAdmin()
                ? Conversation::whereNull('assigned_user_id')->active()->count()
                : 0,
        ];
    }

    public function selectConversation(int $conversationId): void
    {
        $this->selectedConversationId = $conversationId;
    }

    public function getActiveChannelsProperty()
    {
        return Channel::active()->get();
    }

    public function render()
    {
        return view('messaging::livewire.agent-dashboard')
            ->layout('messaging::layouts.admin');
    }
}
