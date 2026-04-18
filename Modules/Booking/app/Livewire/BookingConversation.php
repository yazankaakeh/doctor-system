<?php

namespace Modules\Booking\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Booking\Actions\Booking\CreateBookingConversationAction;
use Modules\Booking\Models\Booking;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\Patient;
use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Enums\MessageStatusEnum;
use Modules\Messaging\Enums\MessageTypeEnum;
use Modules\Messaging\Enums\SenderTypeEnum;
use Modules\Messaging\Events\NewMessageEvent;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;

class BookingConversation extends Component
{
    public Booking $booking;

    public ?Conversation $conversation = null;

    public Collection $messages;

    public string $newMessage = '';

    public string $userType; // 'doctor' or 'patient'

    public bool $isExpanded = true;

    public function mount(Booking $booking, string $userType): void
    {
        $this->booking = $booking;
        $this->userType = $userType;
        $this->conversation = $booking->conversation;
        $this->messages = collect();

        // Create conversation if it doesn't exist yet
        if (! $this->conversation) {
            $this->createConversation();
        }

        $this->loadMessages();
    }

    /**
     * Create a conversation for this booking.
     */
    protected function createConversation(): void
    {
        try {
            $action = app(CreateBookingConversationAction::class);
            $this->conversation = $action->handle($this->booking);

            // Refresh booking relationship
            $this->booking->load('conversation');
        } catch (\Exception $e) {
            \Log::error('Failed to create booking conversation', [
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function loadMessages(): void
    {
        if (! $this->conversation) {
            return;
        }

        $this->messages = $this->conversation
            ->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        $this->markAsRead();
    }

    public function sendMessage(): void
    {
        if (empty(trim($this->newMessage)) || ! $this->conversation) {
            return;
        }

        $sender = $this->getCurrentSender();
        if (! $sender) {
            return;
        }

        // Determine direction based on who is sending
        $direction = $this->userType === 'doctor'
            ? MessageDirectionEnum::OUTBOUND
            : MessageDirectionEnum::INBOUND;

        $senderType = $this->userType === 'doctor'
            ? SenderTypeEnum::USER
            : SenderTypeEnum::CONTACT;

        $message = $this->conversation->messages()->create([
            'sender_type' => $senderType,
            'sender_id' => null, // We don't have a User model for Doctor/Patient
            'direction' => $direction,
            'message_type' => MessageTypeEnum::TEXT,
            'content' => trim($this->newMessage),
            'status' => MessageStatusEnum::DELIVERED,
            'metadata' => [
                'sender_model' => get_class($sender),
                'sender_id' => $sender->id,
                'sender_name' => $sender->name,
                'user_type' => $this->userType,
            ],
        ]);

        // Update conversation last message time
        $this->conversation->update(['last_message_at' => now()]);

        // Clear input
        $this->newMessage = '';

        // Reload messages
        $this->loadMessages();

        // Broadcast event for real-time updates
        event(new NewMessageEvent($message));
    }

    public function getListeners()
    {
        if (! $this->conversation) {
            return [];
        }

        return [
            "echo-private:conversation.{$this->conversation->id},new-message" => 'onNewMessage',
        ];
    }

    public function onNewMessage($data): void
    {
        // Reload messages when a new message is received
        $this->loadMessages();
    }

    public function markAsRead(): void
    {
        if (! $this->conversation) {
            return;
        }

        // Mark all unread messages as read
        $this->conversation->messages()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->conversation->update(['unread_count' => 0]);
    }

    public function toggleExpanded(): void
    {
        $this->isExpanded = ! $this->isExpanded;
    }

    public function getMessageAlignment(Message $message): string
    {
        $metadata = $message->metadata ?? [];

        // System messages are centered
        if ($message->sender_type === SenderTypeEnum::SYSTEM) {
            return 'center';
        }

        // Check if this message was sent by the current user type
        $messageUserType = $metadata['user_type'] ?? null;

        if ($messageUserType === $this->userType) {
            return 'end'; // Own messages on the right
        }

        return 'start'; // Other's messages on the left
    }

    public function getSenderName(Message $message): string
    {
        $metadata = $message->metadata ?? [];

        if ($message->sender_type === SenderTypeEnum::SYSTEM) {
            return __('booking::booking.system');
        }

        return $metadata['sender_name'] ?? ($metadata['user_type'] === 'doctor'
            ? $this->booking->doctor->name
            : $this->booking->patient->name);
    }

    protected function getCurrentSender(): Doctor|Patient|null
    {
        if ($this->userType === 'doctor') {
            return auth('doctor')->user();
        }

        return auth('web')->user();
    }

    public function render(): View
    {
        return view('booking::livewire.booking-conversation');
    }
}
