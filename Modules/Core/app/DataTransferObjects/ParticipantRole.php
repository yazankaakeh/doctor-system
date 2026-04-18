<?php

namespace Modules\Core\DataTransferObjects;

enum ParticipantRole: string
{
    case MODERATOR = 'moderator';
    case PARTICIPANT = 'participant';
    case GUEST = 'guest';

    /**
     * Get the display label.
     */
    public function label(): string
    {
        return match ($this) {
            self::MODERATOR => 'Moderator',
            self::PARTICIPANT => 'Participant',
            self::GUEST => 'Guest',
        };
    }

    /**
     * Check if role has moderation capabilities.
     */
    public function canModerate(): bool
    {
        return $this === self::MODERATOR;
    }
}
