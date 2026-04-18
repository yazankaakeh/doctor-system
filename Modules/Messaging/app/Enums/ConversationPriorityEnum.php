<?php

namespace Modules\Messaging\Enums;

enum ConversationPriorityEnum: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::LOW => __('messaging::conversation.priority.low'),
            self::NORMAL => __('messaging::conversation.priority.normal'),
            self::HIGH => __('messaging::conversation.priority.high'),
            self::URGENT => __('messaging::conversation.priority.urgent'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LOW => 'secondary',
            self::NORMAL => 'primary',
            self::HIGH => 'warning',
            self::URGENT => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::LOW => 'ki-arrow-down',
            self::NORMAL => 'ki-minus',
            self::HIGH => 'ki-arrow-up',
            self::URGENT => 'ki-notification-status',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::URGENT => 4,
            self::HIGH => 3,
            self::NORMAL => 2,
            self::LOW => 1,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
