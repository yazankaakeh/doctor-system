<?php

namespace Modules\Messaging\Enums;

enum ChannelTypeEnum: string
{
    case WHATSAPP = 'whatsapp';
    case TELEGRAM = 'telegram';
    case WEBCHAT = 'webchat';

    public function label(): string
    {
        return match ($this) {
            self::WHATSAPP => __('messaging::channels.whatsapp'),
            self::TELEGRAM => __('messaging::channels.telegram'),
            self::WEBCHAT => __('messaging::channels.webchat'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::WHATSAPP => 'ki-whatsapp',
            self::TELEGRAM => 'ki-send',
            self::WEBCHAT => 'ki-message-notif',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::WHATSAPP => 'success',
            self::TELEGRAM => 'info',
            self::WEBCHAT => 'primary',
        };
    }

    public function bgColor(): string
    {
        return match ($this) {
            self::WHATSAPP => '#25D366',
            self::TELEGRAM => '#0088cc',
            self::WEBCHAT => '#6366f1',
        };
    }

    /**
     * Determines if this channel is admin-only.
     */
    public function isAdminOnly(): bool
    {
        return match ($this) {
            self::WHATSAPP, self::TELEGRAM => true,
            self::WEBCHAT => false,
        };
    }

    /**
     * Check if channel supports templates.
     */
    public function supportsTemplates(): bool
    {
        return match ($this) {
            self::WHATSAPP => true,
            self::TELEGRAM, self::WEBCHAT => false,
        };
    }

    /**
     * Get supported message types for this channel.
     */
    public function supportedMessageTypes(): array
    {
        return match ($this) {
            self::WHATSAPP => ['text', 'image', 'document', 'audio', 'video', 'template', 'location'],
            self::TELEGRAM => ['text', 'image', 'document', 'audio', 'video', 'location'],
            self::WEBCHAT => ['text', 'image', 'document'],
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function adminOnlyChannels(): array
    {
        return [self::WHATSAPP, self::TELEGRAM];
    }

    public static function publicChannels(): array
    {
        return [self::WEBCHAT];
    }
}
