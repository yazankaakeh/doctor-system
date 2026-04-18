<?php

namespace Modules\Booking\Actions\RecurringSchedule;

use Modules\Booking\Repository\Availability\AvailabilityInterface;
use Modules\Booking\Repository\RecurringSchedule\RecurringScheduleInterface;

class DeleteRecurringScheduleAction
{
    public function __construct(
        protected RecurringScheduleInterface $repository,
        protected AvailabilityInterface $availabilityRepository
    ) {}

    public function handle(int $id): bool
    {
        // Clean up future unbooked availabilities generated from this schedule
        $this->availabilityRepository->deleteFutureUnbookedByScheduleId($id);

        return $this->repository->delete($id);
    }
}
