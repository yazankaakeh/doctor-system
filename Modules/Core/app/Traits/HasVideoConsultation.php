<?php

namespace Modules\Core\Traits;

use Modules\Core\Actions\Video\CreateMeetingRoomAction;
use Modules\Core\DataTransferObjects\MeetingRoom;

trait HasVideoConsultation
{
    /**
     * The meeting link attribute name.
     */
    protected function getMeetingLinkColumn(): string
    {
        return 'meeting_link';
    }

    /**
     * The meeting room name attribute.
     */
    protected function getMeetingRoomColumn(): string
    {
        return 'meeting_room_name';
    }

    /**
     * Generate and set meeting room for this model.
     */
    public function generateMeetingRoom(array $options = []): MeetingRoom
    {
        $action = app(CreateMeetingRoomAction::class);

        $identifier = $this->getMeetingIdentifier();
        $room = $action->handle($identifier, $options);

        $this->update([
            $this->getMeetingLinkColumn() => $room->url,
            $this->getMeetingRoomColumn() => $room->name,
        ]);

        return $room;
    }

    /**
     * Get the meeting identifier for this model.
     */
    protected function getMeetingIdentifier(): string
    {
        return sprintf('%s_%d', class_basename($this), $this->getKey());
    }

    /**
     * Check if this model has a meeting room.
     */
    public function hasMeetingRoom(): bool
    {
        return ! empty($this->{$this->getMeetingLinkColumn()});
    }

    /**
     * Get the meeting link.
     */
    public function getMeetingLink(): ?string
    {
        return $this->{$this->getMeetingLinkColumn()};
    }

    /**
     * Get the meeting room name.
     */
    public function getMeetingRoomName(): ?string
    {
        return $this->{$this->getMeetingRoomColumn()};
    }

    /**
     * Get the join meeting URL for doctors.
     */
    public function getDoctorJoinUrl(): string
    {
        return route('video.join', [
            'roomName' => $this->getMeetingRoomName(),
            'booking' => $this->getKey(),
        ]);
    }

    /**
     * Get the join meeting URL for patients.
     */
    public function getPatientJoinUrl(): string
    {
        return route('video.join', [
            'roomName' => $this->getMeetingRoomName(),
            'booking' => $this->getKey(),
        ]);
    }
}
