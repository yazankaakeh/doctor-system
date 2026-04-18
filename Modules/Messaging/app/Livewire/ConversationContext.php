<?php

namespace Modules\Messaging\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Messaging\Models\Conversation;

class ConversationContext extends Component
{
    public ?int $conversationId = null;

    public ?Conversation $conversation = null;

    public $conversable = null;

    public $recentActivities = [];

    public function mount(?int $conversationId = null): void
    {
        if ($conversationId) {
            $this->loadConversation($conversationId);
        }
    }

    #[On('conversation-selected')]
    public function loadConversation(int $conversationId): void
    {
        $this->conversationId = $conversationId;
        $this->conversation = Conversation::with(['channel', 'assignedUser', 'conversable'])->find($conversationId);

        if ($this->conversation && $this->conversation->conversable) {
            $this->conversable = $this->conversation->conversable;
            $this->loadRecentActivities();
        } else {
            $this->conversable = null;
            $this->recentActivities = [];
        }
    }

    protected function loadRecentActivities(): void
    {
        if (! $this->conversable) {
            $this->recentActivities = [];

            return;
        }

        // Try to load activities if the model has them
        if (method_exists($this->conversable, 'activities')) {
            $this->recentActivities = $this->conversable->activities()
                ->latest()
                ->limit(5)
                ->get();
        } else {
            $this->recentActivities = [];
        }
    }

    public function getConversableInfo(): array
    {
        if (! $this->conversable) {
            return [];
        }

        $info = [];

        // Common fields to check
        $fields = ['name', 'email', 'phone', 'mobile', 'full_mobile', 'status', 'country'];

        foreach ($fields as $field) {
            if (isset($this->conversable->$field)) {
                $info[$field] = $this->conversable->$field;
            }
        }

        // Check for related user
        if (is_object($this->conversable) && method_exists($this->conversable, 'user') && $this->conversable->user) {
            $info['user_name'] = $this->conversable->user->name ?? null;
            $info['user_email'] = $this->conversable->user->email ?? null;
        }

        // Check for assigned user
        if (is_object($this->conversable) && method_exists($this->conversable, 'assignedUser') && $this->conversable->assignedUser) {
            $info['assigned_to'] = $this->conversable->assignedUser->name ?? null;
        }

        return $info;
    }

    public function getConversationStats(): array
    {
        if (! $this->conversation) {
            return [];
        }

        return [
            'total_messages' => $this->conversation->messages()->count(),
            'inbound_messages' => $this->conversation->messages()->where('direction', 'inbound')->count(),
            'outbound_messages' => $this->conversation->messages()->where('direction', 'outbound')->count(),
            'first_message_at' => $this->conversation->messages()->oldest()->first()?->created_at,
            'last_message_at' => $this->conversation->last_message_at,
        ];
    }

    public function render()
    {
        return view('messaging::livewire.conversation-context', [
            'conversableInfo' => $this->getConversableInfo(),
            'stats' => $this->getConversationStats(),
        ]);
    }
}
