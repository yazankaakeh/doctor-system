<?php

namespace Modules\Booking\Actions\Availability;

use Modules\Booking\Models\DoctorAvailability;
use Modules\Booking\Repository\Availability\AvailabilityInterface;

class CreateAvailabilityAction
{
    public function __construct(
        private readonly AvailabilityInterface $repository
    ) {}

    public function handle(array $data): DoctorAvailability
    {
        return $this->repository->store($data);
    }
}
