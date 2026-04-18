<?php

namespace Modules\Booking\Repository\ScheduleException;

use Illuminate\Support\Collection;
use Modules\Booking\Models\DoctorScheduleException;

interface ScheduleExceptionInterface
{
    public function getByDoctor(int $doctorId): Collection;

    public function getByDoctorForDateRange(int $doctorId, $startDate, $endDate): Collection;

    public function create(array $data): DoctorScheduleException;

    public function delete(int $id): bool;

    public function find(int $id): DoctorScheduleException;

    public function hasExceptionOnDate(int $doctorId, $date, ?int $scheduleId = null): bool;

    public function getExceptionForDate(int $doctorId, $date, ?int $scheduleId = null): ?DoctorScheduleException;
}
