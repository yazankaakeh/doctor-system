<?php

namespace Modules\Booking\Repository\RecurringSchedule;

use Illuminate\Support\Collection;
use Modules\Booking\Models\DoctorRecurringSchedule;

interface RecurringScheduleInterface
{
    public function getByDoctor(int $doctorId): Collection;

    public function getActiveByDoctor(int $doctorId): Collection;

    public function create(array $data): DoctorRecurringSchedule;

    public function update(int $id, array $data): DoctorRecurringSchedule;

    public function delete(int $id): bool;

    public function find(int $id): DoctorRecurringSchedule;

    public function getEffectiveSchedulesForDateRange(int $doctorId, $startDate, $endDate): Collection;

    public function getByDoctorAndDay(int $doctorId, int $dayOfWeek): Collection;
}
