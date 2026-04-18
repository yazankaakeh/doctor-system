<?php

namespace Modules\Messaging\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Messaging\Services\MessagingService;

class GlobalMessagingIcon extends Component
{
    public int $unreadCount = 0;

    public ?int $userId = null;

    public function mount(): void
    {
        $this->userId = auth()->id();
        $this->loadUnreadCount();
    }

    public function loadUnreadCount(): void
    {
        if (auth()->check()) {
            try {
                $service = app(MessagingService::class);
                $this->unreadCount = $service->getUnreadCount(auth()->id());
            } catch (\Throwable $e) {
                // Fail silently if service is unavailable
                $this->unreadCount = 0;
            }
        }
    }

    #[On('messaging-new-message')]
    public function onNewMessage(): void
    {
        $this->loadUnreadCount();
    }

    #[On('messaging-read')]
    public function onMessagingRead(): void
    {
        $this->loadUnreadCount();
    }

    public function togglePanel(): void
    {
        $this->dispatch('toggle-messaging-panel');
    }

    public function render()
    {
        return view('messaging::livewire.global-messaging-icon');
    }
}
