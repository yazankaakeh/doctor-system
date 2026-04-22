<?php

namespace Modules\Notification\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a row is inserted into the `notifications` table (via the
 * Notification model's `created` lifecycle). Broadcasts on a private
 * channel keyed by the notifiable's class + id, so only that user's
 * authenticated session receives it.
 *
 * The channel name encodes the class as the first 10 chars of its md5
 * to keep the channel identifier URL-safe (namespaces contain
 * backslashes) while preserving uniqueness across User / Admin /
 * Doctor / Patient morph types.
 */
class NotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $notificationId,
        public int $notifiableId,
        public string $notifiableType,
        public array $payload = [],
    ) {}

    public static function hashForType(string $notifiableType): string
    {
        return substr(md5($notifiableType), 0, 10);
    }

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        $hash = self::hashForType($this->notifiableType);

        return [new PrivateChannel("notifications.{$hash}.{$this->notifiableId}")];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    /**
     * Lightweight payload — the client just calls $wire.loadNotifications()
     * to pull the full row, so we only need identifiers on the wire.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->notificationId,
            'payload' => $this->payload,
        ];
    }
}
