<?php

namespace Modules\Booking\Actions\Booking;

use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Booking\Models\Booking;
use Modules\Booking\Notifications\BookingCancelledNotification;
use Modules\Booking\Repository\Booking\BookingInterface;

class CancelBookingAction
{
    public function __construct(
        private readonly BookingInterface $repository
    ) {}

    public function handle(int $bookingId, ?string $reason = null): Booking
    {
        $booking = $this->repository->find($bookingId);

        if (! $booking->canBeCancelled()) {
            throw new \Exception(__('booking::booking.cannot_cancel'));
        }

        $booking = $this->repository->update($bookingId, [
            'status' => BookingStatusEnum::CANCELLED,
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);

        $booking->doctor->notify(new BookingCancelledNotification($booking));

        return $booking;
    }
}
