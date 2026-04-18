<?php

namespace Modules\Messaging\Enums;

enum SenderTypeEnum: string
{
    case USER = 'user';       // Internal agent/admin
    case CONTACT = 'contact'; // External customer/lead
    case SYSTEM = 'system';   // Automated system messages

    public function label(): string
    {
        return match ($this) {
            self::USER => __('messaging::message.sender.user'),
            self::CONTACT => __('messaging::message.sender.contact'),
            self::SYSTEM => __('messaging::message.sender.system'),
        };
    }

    public function isInternal(): bool
    {
        return in_array($this, [self::USER, self::SYSTEM]);
    }

    public function isExternal(): bool
    {
        return $this === self::CONTACT;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
