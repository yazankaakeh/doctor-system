<?php

namespace Modules\Booking\Actions\Booking;

use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Booking\Models\Booking;
use Modules\Booking\Notifications\BookingConfirmedNotification;
use Modules\Booking\Notifications\NewBookingDoctorNotification;
use Modules\Booking\Repository\Booking\BookingInterface;
use Modules\Core\Actions\Video\CreateMeetingRoomAction;

class ConfirmBookingAction
{
    public function __construct(
        private readonly BookingInterface $repository,
        private readonly CreateMeetingRoomAction $createMeetingRoom,
        private readonly ?CreateBookingConversationAction $createConversation = null
    ) {}

    public function handle(int $bookingId): Booking
    {
        $booking = $this->repository->find($bookingId);

        if ($booking->status !== BookingStatusEnum::PENDING) {
            throw new \Exception(__('booking::booking.already_processed'));
        }

        // Generate meeting room using the video service
        $meetingRoom = $this->createMeetingRoom->forBooking(
            $booking->id,
            $booking->doctor_id,
            ['expires_at' => $booking->booking_date->endOfDay()]
        );

        $booking = $this->repository->update($bookingId, [
            'status' => BookingStatusEnum::CONFIRMED,
            'meeting_link' => $meetingRoom->url,
            'meeting_room_name' => $meetingRoom->name,
        ]);

        // Create messaging conversation for doctor-patient communication (if Messaging module is available)
        if ($this->createConversation) {
            try {
                $this->createConversation->handle($booking);
            } catch (\Exception $e) {
                // Log but don't fail if conversation creation fails
                \Log::warning('Failed to create booking conversation', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Send notifications
        $booking->patient->notify(new BookingConfirmedNotification($booking));
        $booking->doctor->notify(new NewBookingDoctorNotification($booking));

        return $booking;
    }
}
