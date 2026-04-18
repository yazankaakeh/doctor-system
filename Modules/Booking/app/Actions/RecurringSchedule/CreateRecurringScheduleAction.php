<?php

namespace Modules\Booking\Actions\RecurringSchedule;

use Modules\Booking\Models\DoctorRecurringSchedule;
use Modules\Booking\Repository\RecurringSchedule\RecurringScheduleInterface;

class CreateRecurringScheduleAction
{
    public function __construct(
        protected RecurringScheduleInterface $repository
    ) {}

    public function handle(array $data): DoctorRecurringSchedule
    {
        return $this->repository->create($data);
    }
}
