<?php

namespace Modules\Booking\Repository\Availability;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Booking\Models\DoctorAvailability;

interface AvailabilityInterface
{
    public function getForDoctor(int $doctorId): LengthAwarePaginator;

    public function getUpcomingForDoctor(int $doctorId): Collection;

    public function store(array $data): DoctorAvailability;

    public function find(int $id): DoctorAvailability;

    public function update(int $id, array $data): DoctorAvailability;

    public function destroy(int $id): void;

    public function getAvailableDates(int $doctorId): Collection;

    public function getAvailableSlots(int $doctorId, string $date): array;

    public function getByDoctorForDateRange(int $doctorId, $startDate, $endDate): Collection;

    public function deleteUnbookedByDoctorAndDate(int $doctorId, $date): int;

    public function deleteFutureUnbookedByScheduleId(int $scheduleId): int;

    public function storeOrUpdateFromRecurring(array $data): DoctorAvailability;
}
