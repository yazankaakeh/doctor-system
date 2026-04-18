<?php

namespace Modules\Core\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

final class MeetingParticipant implements Arrayable, JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ParticipantRole $role,
        public readonly ?string $avatar = null,
        public readonly array $metadata = [],
    ) {}

    /**
     * Create a doctor participant.
     */
    public static function doctor(string $id, string $name, string $email, ?string $avatar = null): self
    {
        return new self(
            id: $id,
            name: $name,
            email: $email,
            role: ParticipantRole::MODERATOR,
            avatar: $avatar,
            metadata: ['type' => 'doctor'],
        );
    }

    /**
     * Create a patient participant.
     */
    public static function patient(string $id, string $name, string $email, ?string $avatar = null): self
    {
        return new self(
            id: $id,
            name: $name,
            email: $email,
            role: ParticipantRole::PARTICIPANT,
            avatar: $avatar,
            metadata: ['type' => 'patient'],
        );
    }

    /**
     * Check if participant is moderator.
     */
    public function isModerator(): bool
    {
        return $this->role === ParticipantRole::MODERATOR;
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role->value,
            'avatar' => $this->avatar,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Serialize to JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
