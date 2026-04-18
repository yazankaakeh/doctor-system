<?php

namespace Modules\Booking\Actions\ScheduleException;

use Modules\Booking\Models\DoctorScheduleException;
use Modules\Booking\Repository\Availability\AvailabilityInterface;
use Modules\Booking\Repository\ScheduleException\ScheduleExceptionInterface;

class CreateScheduleExceptionAction
{
    public function __construct(
        protected ScheduleExceptionInterface $repository,
        protected AvailabilityInterface $availabilityRepository
    ) {}

    public function handle(array $data): DoctorScheduleException
    {
        $exception = $this->repository->create($data);

        // If this is a skip exception, delete unbooked availabilities for that date
        if ($exception->isSkip()) {
            $this->availabilityRepository->deleteUnbookedByDoctorAndDate(
                $exception->doctor_id,
                $exception->exception_date
            );
        }

        return $exception;
    }
}
