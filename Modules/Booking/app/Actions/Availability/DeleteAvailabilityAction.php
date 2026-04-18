<?php

namespace Modules\Booking\Actions\Availability;

use Modules\Booking\Repository\Availability\AvailabilityInterface;

class DeleteAvailabilityAction
{
    public function __construct(
        private readonly AvailabilityInterface $repository
    ) {}

    public function handle(int $id): void
    {
        $this->repository->destroy($id);
    }
}
