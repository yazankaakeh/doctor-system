<?php

namespace Modules\Messaging\Livewire\Lead;

use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Services\MessagingService;

class LeadConversationView extends Component
{
    public ?int $conversationId = null;

    public ?Conversation $conversation = null;

    public $messages = [];

    public function mount(?int $conversationId = null): void
    {
        if ($conversationId) {
            $this->loadConversation($conversationId);
        }
    }

    public function loadConversation(int $conversationId): void
    {
        $this->conversationId = $conversationId;
        $this->conversation = Conversation::with(['channel', 'assignedUser'])->find($conversationId);

        if ($this->conversation) {
            $this->loadMessages();
        }
    }

    public function loadMessages(): void
    {
        if (! $this->conversation) {
            return;
        }

        $service = app(MessagingService::class);
        $this->messages = $service->getMessages($this->conversation, 50);

        // Mark as read
        $service->markAsRead($this->conversation);
    }

    #[On('echo-private:conversation.{conversationId},new-message')]
    public function onNewMessage(): void
    {
        $this->loadMessages();
    }

    #[On('message-sent')]
    public function onMessageSent(): void
    {
        $this->loadMessages();
    }

    public function render()
    {
        return view('messaging::livewire.lead.lead-conversation-view');
    }
}
