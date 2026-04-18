<?php

namespace Modules\Booking\Actions\RecurringSchedule;

use Modules\Booking\Models\DoctorRecurringSchedule;
use Modules\Booking\Repository\Availability\AvailabilityInterface;
use Modules\Booking\Repository\RecurringSchedule\RecurringScheduleInterface;

class UpdateRecurringScheduleAction
{
    public function __construct(
        protected RecurringScheduleInterface $repository,
        protected AvailabilityInterface $availabilityRepository
    ) {}

    public function handle(int $id, array $data): DoctorRecurringSchedule
    {
        $schedule = $this->repository->update($id, $data);

        // If schedule was deactivated, optionally clean up future unbooked slots
        if (isset($data['is_active']) && !$data['is_active']) {
            $this->availabilityRepository->deleteFutureUnbookedByScheduleId($id);
        }

        return $schedule;
    }
}
