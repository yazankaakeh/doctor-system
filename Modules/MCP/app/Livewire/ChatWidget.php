<?php

namespace Modules\MCP\Livewire;

use Illuminate\Support\Str;
use Livewire\Component;
use Modules\MCP\Actions\CreateConversationAction;
use Modules\MCP\Actions\ProcessChatMessageAction;
use Modules\MCP\Repository\ChatMessage\ChatMessageInterface;

class ChatWidget extends Component
{
    public $sessionId;

    public $conversationId;

    public $messages = [];

    public $newMessage = '';

    public $isOpen = false;

    public $isLoading = false;

    public $isTyping = false;

    protected $rules = [
        'newMessage' => 'required|string|min:1|max:5000',
    ];

    public function mount()
    {
        // Get or create session ID
        $this->sessionId = session()->get('chat_session_id');

        if (! $this->sessionId) {
            $this->sessionId = Str::uuid()->toString();
            session()->put('chat_session_id', $this->sessionId);
        }

        // Initialize conversation
        $this->initializeConversation();
    }

    public function initializeConversation()
    {
        $createAction = app(CreateConversationAction::class);

        $conversation = $createAction->handle([
            'session_id' => $this->sessionId,
        ]);

        $this->conversationId = $conversation->id;

        // Load existing messages
        $this->loadMessages();

        // Add greeting if no messages
        if (empty($this->messages)) {
            $this->messages[] = [
                'id' => 0,
                'message' => config('mcp.system_prompts.greeting'),
                'is_from_bot' => true,
                'created_at' => now()->toIso8601String(),
            ];
        }
    }

    public function loadMessages()
    {
        $messageRepository = app(ChatMessageInterface::class);
        $messages = $messageRepository->getByConversation($this->conversationId);

        $this->messages = $messages->map(function ($msg) {
            return [
                'id' => $msg->id,
                'message' => $msg->message,
                'is_from_bot' => $msg->is_from_bot,
                'created_at' => $msg->created_at->toIso8601String(),
            ];
        })->toArray();
    }

    public function sendMessage()
    {
        $this->validate();

        $this->isLoading = true;
        $this->isTyping = true;

        // Add user message to UI immediately
        $userMessage = $this->newMessage;
        $this->messages[] = [
            'id' => 'temp_'.time(),
            'message' => $userMessage,
            'is_from_bot' => false,
            'created_at' => now()->toIso8601String(),
        ];

        $this->newMessage = '';

        // Process message and get AI response
        try {
            $processAction = app(ProcessChatMessageAction::class);
            $result = $processAction->handle($this->conversationId, $userMessage);

            // Remove temp message and add real messages
            $this->loadMessages();

            $this->isTyping = false;
        } catch (\Exception $e) {
            $this->messages[] = [
                'id' => 'error_'.time(),
                'message' => 'Sorry, I encountered an error. Please try again.',
                'is_from_bot' => true,
                'created_at' => now()->toIso8601String(),
            ];

            $this->isTyping = false;
        }

        $this->isLoading = false;

        // Scroll to bottom
        $this->dispatch('scrollToBottom');
    }

    public function toggleChat()
    {
        $this->isOpen = ! $this->isOpen;

        if ($this->isOpen) {
            $this->dispatch('chatOpened');
        }
    }

    public function closeChat()
    {
        $this->isOpen = false;
    }

    public function render()
    {
        return view('mcp::livewire.chat-widget');
    }
}
