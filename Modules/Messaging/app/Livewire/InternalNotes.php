<?php

namespace Modules\Messaging\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\ConversationNote;
use Modules\Messaging\Services\MessagingService;

class InternalNotes extends Component
{
    public ?int $conversationId = null;

    public string $newNote = '';

    public $notes = [];

    public bool $isAdding = false;

    protected $rules = [
        'newNote' => 'required|string|max:2000',
    ];

    public function mount(?int $conversationId = null): void
    {
        if ($conversationId) {
            $this->conversationId = $conversationId;
            $this->loadNotes();
        }
    }

    #[On('conversation-selected')]
    public function loadConversation(int $conversationId): void
    {
        $this->conversationId = $conversationId;
        $this->loadNotes();
    }

    public function loadNotes(): void
    {
        if (! $this->conversationId) {
            return;
        }

        $conversation = Conversation::find($this->conversationId);
        if ($conversation) {
            $service = app(MessagingService::class);
            $this->notes = $service->getNotes($conversation);
        }
    }

    public function addNote(): void
    {
        $this->validate();

        if (! $this->conversationId) {
            return;
        }

        $conversation = Conversation::find($this->conversationId);
        if (! $conversation) {
            return;
        }

        $service = app(MessagingService::class);
        $service->addNote($conversation, auth()->user(), $this->newNote);

        $this->newNote = '';
        $this->isAdding = false;
        $this->loadNotes();
    }

    public function deleteNote(int $noteId): void
    {
        $note = ConversationNote::find($noteId);

        if ($note && $note->isOwnedBy(auth()->user())) {
            $note->delete();
            $this->loadNotes();
        }
    }

    public function toggleAddForm(): void
    {
        $this->isAdding = ! $this->isAdding;

        if (! $this->isAdding) {
            $this->newNote = '';
        }
    }

    public function render()
    {
        return view('messaging::livewire.internal-notes');
    }
}
