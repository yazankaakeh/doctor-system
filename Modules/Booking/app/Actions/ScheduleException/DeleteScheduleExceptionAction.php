<?php

namespace Modules\Booking\Actions\ScheduleException;

use Modules\Booking\Repository\ScheduleException\ScheduleExceptionInterface;

class DeleteScheduleExceptionAction
{
    public function __construct(
        protected ScheduleExceptionInterface $repository
    ) {}

    public function handle(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
