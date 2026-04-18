<?php

namespace Modules\Messaging\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Messaging\Enums\ConversationStatusEnum;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Services\MessagingService;

class ConversationThread extends Component
{
    public ?int $conversationId = null;

    public ?Conversation $conversation = null;

    public $messages = [];

    public int $loadedMessages = 50;

    public bool $hasMoreMessages = false;

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

        if ($this->conversation) {
            $this->loadMessages();
            $this->markAsRead();
        }
    }

    public function loadMessages(): void
    {
        if (! $this->conversation) {
            return;
        }

        $service = app(MessagingService::class);
        $messages = $service->getMessages($this->conversation, $this->loadedMessages);
        $this->hasMoreMessages = $messages->count() >= $this->loadedMessages;

        // Reverse so oldest messages appear first (at top), newest at bottom
        $this->messages = $messages->reverse()->values();

        // Dispatch event to trigger scroll to bottom
        $this->dispatch('conversation-loaded');
    }

    public function loadMoreMessages(): void
    {
        $this->loadedMessages += 50;
        $this->loadMessages();
    }

    public function markAsRead(): void
    {
        if ($this->conversation) {
            $service = app(MessagingService::class);
            $service->markAsRead($this->conversation);
            $this->dispatch('messaging-read');
        }
    }

    public function changeStatus(string $status): void
    {
        if (! $this->conversation) {
            return;
        }

        $newStatus = ConversationStatusEnum::tryFrom($status);
        if ($newStatus) {
            $service = app(MessagingService::class);

            if ($newStatus === ConversationStatusEnum::CLOSED) {
                $service->closeConversation($this->conversation);
            } else {
                $this->conversation->update(['status' => $newStatus]);
            }

            $this->conversation->refresh();
        }
    }

    public function assignToMe(): void
    {
        if (! $this->conversation) {
            return;
        }

        $service = app(MessagingService::class);
        $service->assignConversation($this->conversation, auth()->user());
        $this->conversation->refresh();
    }

    #[On('messaging-new-message')]
    public function onNewMessage(): void
    {
        $this->loadMessages();
        $this->markAsRead();
    }

    #[On('message-sent')]
    public function onMessageSent(): void
    {
        $this->loadMessages();
    }

    public function render()
    {
        return view('messaging::livewire.conversation-thread');
    }
}
