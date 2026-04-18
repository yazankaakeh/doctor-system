<?php

namespace Modules\Messaging\Services;

use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Enums\MessageTypeEnum;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;

class ChatbotService
{
    /**
     * Chatbot configuration.
     */
    protected array $config = [];

    /**
     * Keywords to responses mapping.
     */
    protected array $keywordResponses = [];

    /**
     * Menu options.
     */
    protected array $menuOptions = [];

    public function __construct()
    {
        $this->loadConfiguration();
    }

    /**
     * Load chatbot configuration.
     */
    protected function loadConfiguration(): void
    {
        $this->config = config('messaging.chatbot', [
            'enabled' => false,
            'greeting' => 'Hello! How can I help you today?',
            'fallback' => "I'm sorry, I didn't understand that. Please type 'menu' for options or wait for an agent.",
            'transfer_keywords' => ['agent', 'human', 'help', 'support'],
        ]);

        // Define keyword responses
        $this->keywordResponses = [
            'hello' => 'Hello! How can I help you today? Type "menu" to see available options.',
            'hi' => 'Hi there! How can I assist you? Type "menu" to see available options.',
            'menu' => $this->getMenuResponse(),
            'hours' => 'Our business hours are Monday to Friday, 9 AM to 6 PM.',
            'contact' => 'You can reach us at support@example.com or call +1234567890.',
            'thanks' => "You're welcome! Is there anything else I can help you with?",
            'bye' => 'Goodbye! Have a great day!',
        ];

        // Menu options for interactive lists
        $this->menuOptions = [
            [
                'id' => 'products',
                'title' => 'Our Products',
                'description' => 'Learn about our products and services',
            ],
            [
                'id' => 'pricing',
                'title' => 'Pricing',
                'description' => 'Get pricing information',
            ],
            [
                'id' => 'support',
                'title' => 'Support',
                'description' => 'Get help with an issue',
            ],
            [
                'id' => 'agent',
                'title' => 'Talk to Agent',
                'description' => 'Connect with a human agent',
            ],
        ];
    }

    /**
     * Process incoming message and generate response.
     */
    public function processMessage(Message $message): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $conversation = $message->conversation;

        // Check if chatbot should handle this conversation
        if (! $this->shouldHandle($conversation)) {
            return null;
        }

        $content = strtolower(trim($message->content));

        // Check for transfer keywords
        if ($this->shouldTransferToAgent($content)) {
            $this->requestAgentTransfer($conversation);

            return "I'll connect you with an agent shortly. Please wait...";
        }

        // Check for keyword responses
        foreach ($this->keywordResponses as $keyword => $response) {
            if (str_contains($content, $keyword)) {
                return $response;
            }
        }

        // Handle menu selections
        $menuResponse = $this->handleMenuSelection($content);
        if ($menuResponse) {
            return $menuResponse;
        }

        // Fallback response
        return $this->config['fallback'] ?? "I didn't understand that. Type 'menu' for options.";
    }

    /**
     * Check if chatbot is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    /**
     * Check if chatbot should handle this conversation.
     */
    public function shouldHandle(Conversation $conversation): bool
    {
        // Don't handle if agent is assigned
        if ($conversation->assigned_to) {
            return false;
        }

        // Don't handle if chatbot is disabled for this conversation
        if ($conversation->metadata['chatbot_disabled'] ?? false) {
            return false;
        }

        return true;
    }

    /**
     * Check if should transfer to agent.
     */
    protected function shouldTransferToAgent(string $content): bool
    {
        $transferKeywords = $this->config['transfer_keywords'] ?? ['agent', 'human'];

        foreach ($transferKeywords as $keyword) {
            if (str_contains($content, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Request transfer to human agent.
     */
    protected function requestAgentTransfer(Conversation $conversation): void
    {
        // Mark conversation for agent assignment
        $conversation->update([
            'metadata' => array_merge($conversation->metadata ?? [], [
                'chatbot_transfer_requested' => true,
                'chatbot_transfer_at' => now()->toIso8601String(),
            ]),
        ]);

        // Trigger auto-assignment
        app(AutoAssignmentService::class)->assign($conversation);
    }

    /**
     * Handle menu selection.
     */
    protected function handleMenuSelection(string $content): ?string
    {
        return match ($content) {
            'products', '1' => $this->getProductsInfo(),
            'pricing', '2' => $this->getPricingInfo(),
            'support', '3' => $this->getSupportInfo(),
            default => null,
        };
    }

    /**
     * Get menu response.
     */
    protected function getMenuResponse(): string
    {
        return "Here are your options:\n\n".
            "1. Products - Learn about our products\n".
            "2. Pricing - Get pricing information\n".
            "3. Support - Get help with an issue\n".
            "4. Agent - Talk to a human agent\n\n".
            'Reply with the number or keyword to select an option.';
    }

    /**
     * Get products info.
     */
    protected function getProductsInfo(): string
    {
        return "We offer a variety of products and services. Visit our website for more details or type 'agent' to speak with our team.";
    }

    /**
     * Get pricing info.
     */
    protected function getPricingInfo(): string
    {
        return "Our pricing varies by product and service. For a detailed quote, please type 'agent' to connect with our sales team.";
    }

    /**
     * Get support info.
     */
    protected function getSupportInfo(): string
    {
        return "For technical support, please describe your issue or type 'agent' to connect with a support specialist.";
    }

    /**
     * Send chatbot response.
     */
    public function sendResponse(Conversation $conversation, string $content): Message
    {
        return Message::create([
            'conversation_id' => $conversation->id,
            'direction' => MessageDirectionEnum::OUTBOUND,
            'message_type' => MessageTypeEnum::TEXT,
            'content' => $content,
            'sender_id' => null, // System/chatbot message
            'metadata' => [
                'is_chatbot' => true,
            ],
        ]);
    }

    /**
     * Disable chatbot for a conversation.
     */
    public function disableForConversation(Conversation $conversation): void
    {
        $conversation->update([
            'metadata' => array_merge($conversation->metadata ?? [], [
                'chatbot_disabled' => true,
            ]),
        ]);
    }

    /**
     * Enable chatbot for a conversation.
     */
    public function enableForConversation(Conversation $conversation): void
    {
        $metadata = $conversation->metadata ?? [];
        unset($metadata['chatbot_disabled']);

        $conversation->update(['metadata' => $metadata]);
    }

    /**
     * Get chatbot statistics.
     */
    public function getStatistics(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'messages_handled' => Message::where('metadata->is_chatbot', true)->count(),
            'transfers_requested' => Conversation::where('metadata->chatbot_transfer_requested', true)->count(),
        ];
    }
}
