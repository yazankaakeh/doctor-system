<?php

namespace Modules\Messaging\Enums;

enum MessageDirectionEnum: string
{
    case INBOUND = 'inbound';
    case OUTBOUND = 'outbound';

    public function label(): string
    {
        return match ($this) {
            self::INBOUND => __('messaging::message.direction.inbound'),
            self::OUTBOUND => __('messaging::message.direction.outbound'),
        };
    }

    public function isInbound(): bool
    {
        return $this === self::INBOUND;
    }

    public function isOutbound(): bool
    {
        return $this === self::OUTBOUND;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
