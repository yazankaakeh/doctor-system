<?php

namespace Modules\Core\Contracts;

use Modules\Core\DataTransferObjects\MeetingParticipant;
use Modules\Core\DataTransferObjects\MeetingRoom;

interface VideoServiceInterface
{
    /**
     * Generate a meeting room for a consultation.
     */
    public function createRoom(string $identifier, array $options = []): MeetingRoom;

    /**
     * Get the meeting room URL for a participant.
     */
    public function getRoomUrl(string $roomName, MeetingParticipant $participant): string;

    /**
     * Generate a JWT token for authenticated access (if supported).
     */
    public function generateToken(string $roomName, MeetingParticipant $participant, int $expiresInMinutes = 60): ?string;

    /**
     * Check if the room exists and is active.
     */
    public function isRoomActive(string $roomName): bool;

    /**
     * Get the embed configuration for the video room.
     */
    public function getEmbedConfig(string $roomName, MeetingParticipant $participant): array;

    /**
     * Get the provider name.
     */
    public function getProviderName(): string;

    /**
     * Get the provider domain.
     */
    public function getDomain(): string;
}
