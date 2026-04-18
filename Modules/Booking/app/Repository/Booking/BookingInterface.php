<?php

namespace Modules\Booking\Repository\Booking;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Booking\Models\Booking;

interface BookingInterface
{
    public function getForPatient(int $patientId): LengthAwarePaginator;

    public function getForDoctor(int $doctorId): LengthAwarePaginator;

    public function getUpcomingForDoctor(int $doctorId): LengthAwarePaginator;

    public function getAllForDoctor(int $doctorId): Collection;

    public function store(array $data): Booking;

    public function find(int $id): Booking;

    public function update(int $id, array $data): Booking;

    public function isSlotAvailable(int $doctorId, string $date, string $startTime): bool;
}
