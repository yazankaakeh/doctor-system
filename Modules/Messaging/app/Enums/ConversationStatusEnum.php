<?php

namespace Modules\Messaging\Enums;

enum ConversationStatusEnum: string
{
    case OPEN = 'open';
    case PENDING = 'pending';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => __('messaging::conversation.status.open'),
            self::PENDING => __('messaging::conversation.status.pending'),
            self::RESOLVED => __('messaging::conversation.status.resolved'),
            self::CLOSED => __('messaging::conversation.status.closed'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::OPEN => 'success',
            self::PENDING => 'warning',
            self::RESOLVED => 'info',
            self::CLOSED => 'secondary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::OPEN => 'ki-message-text',
            self::PENDING => 'ki-time',
            self::RESOLVED => 'ki-check-circle',
            self::CLOSED => 'ki-lock',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::OPEN, self::PENDING]);
    }

    public function canSendMessage(): bool
    {
        return $this !== self::CLOSED;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function activeStatuses(): array
    {
        return [self::OPEN, self::PENDING];
    }
}
