<?php

namespace Modules\Booking\Actions\Booking;

use Modules\Booking\Repository\Booking\BookingInterface;

class CheckSlotAvailabilityAction
{
    public function __construct(
        private readonly BookingInterface $repository
    ) {}

    public function handle(int $doctorId, string $date, string $startTime): bool
    {
        return $this->repository->isSlotAvailable($doctorId, $date, $startTime);
    }
}
