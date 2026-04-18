<?php

namespace Modules\Core\Actions\Video;

use Modules\Core\Contracts\VideoServiceInterface;
use Modules\Core\DataTransferObjects\MeetingRoom;

class CreateMeetingRoomAction
{
    public function __construct(
        protected VideoServiceInterface $videoService
    ) {}

    /**
     * Create a new meeting room.
     */
    public function handle(string $identifier, array $options = []): MeetingRoom
    {
        return $this->videoService->createRoom($identifier, $options);
    }

    /**
     * Create a meeting room for a booking.
     */
    public function forBooking(int $bookingId, int $doctorId, array $options = []): MeetingRoom
    {
        $identifier = sprintf('booking_%d_doctor_%d', $bookingId, $doctorId);

        return $this->handle($identifier, $options);
    }

    /**
     * Create a meeting room with expiration.
     */
    public function withExpiration(string $identifier, \DateTimeInterface $expiresAt, array $options = []): MeetingRoom
    {
        $options['expires_at'] = $expiresAt;

        return $this->handle($identifier, $options);
    }
}
