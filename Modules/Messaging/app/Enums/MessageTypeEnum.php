<?php

namespace Modules\Messaging\Enums;

enum MessageTypeEnum: string
{
    case TEXT = 'text';
    case IMAGE = 'image';
    case DOCUMENT = 'document';
    case AUDIO = 'audio';
    case VIDEO = 'video';
    case TEMPLATE = 'template';
    case LOCATION = 'location';
    case STICKER = 'sticker';
    case CONTACT = 'contact';
    case INTERNAL_NOTE = 'internal_note';
    case INTERACTIVE = 'interactive';

    public function label(): string
    {
        return match ($this) {
            self::TEXT => __('messaging::message.type.text'),
            self::IMAGE => __('messaging::message.type.image'),
            self::DOCUMENT => __('messaging::message.type.document'),
            self::AUDIO => __('messaging::message.type.audio'),
            self::VIDEO => __('messaging::message.type.video'),
            self::TEMPLATE => __('messaging::message.type.template'),
            self::LOCATION => __('messaging::message.type.location'),
            self::STICKER => __('messaging::message.type.sticker'),
            self::CONTACT => __('messaging::message.type.contact'),
            self::INTERNAL_NOTE => __('messaging::message.type.internal_note'),
            self::INTERACTIVE => __('messaging::message.type.interactive'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::TEXT => 'ki-sms',
            self::IMAGE => 'ki-picture',
            self::DOCUMENT => 'ki-document',
            self::AUDIO => 'ki-microphone',
            self::VIDEO => 'ki-screen',
            self::TEMPLATE => 'ki-element-11',
            self::LOCATION => 'ki-geolocation',
            self::STICKER => 'ki-emoji-happy',
            self::CONTACT => 'ki-user',
            self::INTERNAL_NOTE => 'ki-note-2',
            self::INTERACTIVE => 'ki-menu',
        };
    }

    public function isInternal(): bool
    {
        return $this === self::INTERNAL_NOTE;
    }

    public function isMedia(): bool
    {
        return in_array($this, [self::IMAGE, self::DOCUMENT, self::AUDIO, self::VIDEO, self::STICKER]);
    }

    public function requiresMediaUpload(): bool
    {
        return in_array($this, [self::IMAGE, self::DOCUMENT, self::AUDIO, self::VIDEO]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function mediaTypes(): array
    {
        return [self::IMAGE, self::DOCUMENT, self::AUDIO, self::VIDEO];
    }
}
