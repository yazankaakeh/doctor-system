<?php

namespace Modules\Notification\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\Notification\Models\Notification;

class NotificationDropdown extends Component
{
    public int $unreadCount = 0;

    public $notifications = [];

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $user = Auth::user();

        if (!$user) {
            $this->notifications = [];
            $this->unreadCount = 0;
            return;
        }

        $this->notifications = Notification::query()
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => is_string($notification->data) ? json_decode($notification->data, true) : $notification->data,
                    'read' => (bool) $notification->read,
                    'created_at' => $notification->created_at,
                ];
            })
            ->toArray();

        $this->unreadCount = Notification::query()
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->where('read', false)
            ->count();
    }

    public function markAsRead(int $notificationId): void
    {
        $user = Auth::user();

        if (!$user) {
            return;
        }

        Notification::query()
            ->where('id', $notificationId)
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->update(['read' => true]);

        $this->loadNotifications();
    }

    public function markAllAsRead(): void
    {
        $user = Auth::user();

        if (!$user) {
            return;
        }

        Notification::query()
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->where('read', false)
            ->update(['read' => true]);

        $this->loadNotifications();
    }

    public function render()
    {
        return view('notification::livewire.notification-dropdown');
    }
}
