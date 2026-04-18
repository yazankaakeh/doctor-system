<?php

namespace Modules\Messaging\Services;

use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;

class WhatsAppWindowService
{
    /**
     * WhatsApp allows free-form messaging for 24 hours after the last customer message.
     */
    protected const WINDOW_HOURS = 24;

    /**
     * Check if the conversation is within the 24-hour messaging window.
     */
    public function isWithinWindow(Conversation $conversation): bool
    {
        $lastCustomerMessage = $this->getLastCustomerMessage($conversation);

        if (! $lastCustomerMessage) {
            // No customer message = no window, must use template
            return false;
        }

        return $lastCustomerMessage->created_at->addHours(self::WINDOW_HOURS)->isFuture();
    }

    /**
     * Get remaining time in the messaging window.
     */
    public function getRemainingWindowTime(Conversation $conversation): ?array
    {
        $lastCustomerMessage = $this->getLastCustomerMessage($conversation);

        if (! $lastCustomerMessage) {
            return null;
        }

        $windowEnd = $lastCustomerMessage->created_at->addHours(self::WINDOW_HOURS);

        if ($windowEnd->isPast()) {
            return null;
        }

        $remaining = now()->diff($windowEnd);

        return [
            'hours' => $remaining->h + ($remaining->days * 24),
            'minutes' => $remaining->i,
            'expires_at' => $windowEnd,
            'is_expiring_soon' => $remaining->h < 2 && $remaining->days === 0,
        ];
    }

    /**
     * Check if template is required for sending a message.
     */
    public function requiresTemplate(Conversation $conversation): bool
    {
        return ! $this->isWithinWindow($conversation);
    }

    /**
     * Get the last message sent by the customer (inbound).
     */
    protected function getLastCustomerMessage(Conversation $conversation): ?Message
    {
        return $conversation->messages()
            ->where('direction', MessageDirectionEnum::INBOUND)
            ->latest()
            ->first();
    }

    /**
     * Get window status for display in UI.
     */
    public function getWindowStatus(Conversation $conversation): array
    {
        $isOpen = $this->isWithinWindow($conversation);
        $remaining = $this->getRemainingWindowTime($conversation);

        return [
            'is_open' => $isOpen,
            'requires_template' => ! $isOpen,
            'remaining' => $remaining,
            'status_label' => $this->getStatusLabel($isOpen, $remaining),
            'status_color' => $this->getStatusColor($isOpen, $remaining),
        ];
    }

    protected function getStatusLabel(bool $isOpen, ?array $remaining): string
    {
        if (! $isOpen) {
            return __('messaging::messages.window_closed');
        }

        if ($remaining && $remaining['is_expiring_soon']) {
            return __('messaging::messages.window_expiring_soon', [
                'hours' => $remaining['hours'],
                'minutes' => $remaining['minutes'],
            ]);
        }

        return __('messaging::messages.window_open', [
            'hours' => $remaining['hours'] ?? 0,
        ]);
    }

    protected function getStatusColor(bool $isOpen, ?array $remaining): string
    {
        if (! $isOpen) {
            return 'danger';
        }

        if ($remaining && $remaining['is_expiring_soon']) {
            return 'warning';
        }

        return 'success';
    }
}
