<?php

namespace Modules\Booking\Actions\Booking;

use Modules\Booking\Models\Booking;
use Modules\Core\DataTransferObjects\MeetingRoom;

class GenerateMeetingLinkAction
{
    /**
     * Generate a meeting link for a booking.
     */
    public function handle(Booking $booking): MeetingRoom
    {
        // Use the trait method to generate and save the meeting room
        return $booking->generateMeetingRoom([
            'expires_at' => $booking->booking_date->endOfDay(),
        ]);
    }

    /**
     * Generate meeting link if not already set.
     */
    public function handleIfNeeded(Booking $booking): ?MeetingRoom
    {
        if ($booking->hasMeetingRoom()) {
            return null;
        }

        return $this->handle($booking);
    }
}
