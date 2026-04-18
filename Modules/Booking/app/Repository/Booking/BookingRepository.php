<?php

namespace Modules\Booking\Repository\Booking;

use App\Enum\Pagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Booking\Models\Booking;

class BookingRepository implements BookingInterface
{
    public function getForPatient(int $patientId): LengthAwarePaginator
    {
        return Booking::query()
            ->forPatient($patientId)
            ->with(['doctor', 'doctor.medicalSpecialty', 'payment'])
            ->orderBy('booking_date', 'desc')
            ->paginate(Pagination::PAG->value);
    }

    public function getForDoctor(int $doctorId): LengthAwarePaginator
    {
        return Booking::query()
            ->forDoctor($doctorId)
            ->with(['patient'])
            ->orderBy('booking_date', 'desc')
            ->paginate(Pagination::PAG->value);
    }

    public function getUpcomingForDoctor(int $doctorId): LengthAwarePaginator
    {
        return Booking::query()
            ->forDoctor($doctorId)
            ->upcoming()
            ->active()
            ->with(['patient'])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->paginate(Pagination::PAG->value);
    }

    public function getAllForDoctor(int $doctorId): Collection
    {
        return Booking::query()
            ->forDoctor($doctorId)
            ->with(['patient'])
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time')
            ->get();
    }

    public function store(array $data): Booking
    {
        return Booking::create($data);
    }

    public function find(int $id): Booking
    {
        return Booking::query()->findOrFail($id);
    }

    public function update(int $id, array $data): Booking
    {
        $booking = $this->find($id);
        $booking->update($data);

        return $booking;
    }

    public function isSlotAvailable(int $doctorId, string $date, string $startTime): bool
    {
        return ! Booking::query()
            ->where('doctor_id', $doctorId)
            ->where('booking_date', $date)
            ->where('start_time', $startTime)
            ->whereIn('status', [
                BookingStatusEnum::PENDING->value,
                BookingStatusEnum::CONFIRMED->value,
            ])
            ->exists();
    }
}
