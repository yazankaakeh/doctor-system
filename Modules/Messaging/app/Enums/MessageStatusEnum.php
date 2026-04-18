<?php

namespace Modules\Messaging\Enums;

enum MessageStatusEnum: string
{
    case PENDING = 'pending';
    case QUEUED = 'queued';
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case READ = 'read';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('messaging::message.status.pending'),
            self::QUEUED => __('messaging::message.status.queued'),
            self::SENT => __('messaging::message.status.sent'),
            self::DELIVERED => __('messaging::message.status.delivered'),
            self::READ => __('messaging::message.status.read'),
            self::FAILED => __('messaging::message.status.failed'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'secondary',
            self::QUEUED => 'info',
            self::SENT => 'primary',
            self::DELIVERED => 'success',
            self::READ => 'success',
            self::FAILED => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'ki-time',
            self::QUEUED => 'ki-time',
            self::SENT => 'ki-check',
            self::DELIVERED => 'ki-double-check',
            self::READ => 'ki-double-check',
            self::FAILED => 'ki-cross-circle',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::READ, self::FAILED]);
    }

    public function isSuccess(): bool
    {
        return in_array($this, [self::SENT, self::DELIVERED, self::READ]);
    }

    public function canRetry(): bool
    {
        return $this === self::FAILED;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function successStatuses(): array
    {
        return [self::SENT, self::DELIVERED, self::READ];
    }
}
