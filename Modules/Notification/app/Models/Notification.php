<?php

namespace Modules\Notification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Log;
use Modules\Notification\Events\NotificationCreated;

class Notification extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'notifiable_id',
        'notifiable_type',
        'type',
        'read',
        'action_key',
        'action_value',
        'data',
    ];

    /**
     * Column casts. `data` is a longText column but callers pass arrays
     * (SendDBChannel::sendDBNotification). Without a cast, Laravel would
     * attempt to write the PHP array directly and either raise
     * "Array to string conversion" or silently persist the literal
     * string "Array". Casting to 'array' makes JSON encode on write and
     * decode on read so the dropdown can do $notification->data['title']
     * directly.
     */
    protected $casts = [
        'data' => 'array',
        'read' => 'boolean',
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Hook the `created` lifecycle so every new row broadcasts a
     * lightweight realtime event to the recipient's private channel.
     * Failures are swallowed — broadcasting must never prevent the
     * notification from being saved.
     */
    protected static function booted(): void
    {
        static::created(function (self $notification): void {
            try {
                NotificationCreated::dispatch(
                    (int) $notification->id,
                    (int) $notification->notifiable_id,
                    (string) $notification->notifiable_type,
                    is_string($notification->data)
                        ? (json_decode($notification->data, true) ?: [])
                        : (is_array($notification->data) ? $notification->data : []),
                );
            } catch (\Throwable $e) {
                Log::warning('Failed to broadcast NotificationCreated event', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }
}
