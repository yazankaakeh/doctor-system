<?php

namespace Modules\Booking\Repository\Availability;

use App\Enum\Pagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Booking\Models\DoctorAvailability;

class AvailabilityRepository implements AvailabilityInterface
{
    public function getForDoctor(int $doctorId): LengthAwarePaginator
    {
        return DoctorAvailability::query()
            ->forDoctor($doctorId)
            ->orderBy('date', 'desc')
            ->paginate(Pagination::PAG->value);
    }

    public function getUpcomingForDoctor(int $doctorId): Collection
    {
        return DoctorAvailability::query()
            ->forDoctor($doctorId)
            ->active()
            ->upcoming()
            ->orderBy('date')
            ->get();
    }

    public function store(array $data): DoctorAvailability
    {
        return DoctorAvailability::create($data);
    }

    public function find(int $id): DoctorAvailability
    {
        return DoctorAvailability::query()->findOrFail($id);
    }

    public function update(int $id, array $data): DoctorAvailability
    {
        $availability = $this->find($id);
        $availability->update($data);

        return $availability;
    }

    public function destroy(int $id): void
    {
        $availability = $this->find($id);
        $availability->delete();
    }

    public function getAvailableDates(int $doctorId): Collection
    {
        return DoctorAvailability::query()
            ->forDoctor($doctorId)
            ->active()
            ->upcoming()
            ->orderBy('date')
            ->get()
            ->filter(fn (DoctorAvailability $a) => count($a->getAvailableSlots()) > 0)
            ->pluck('date')
            ->unique();
    }

    public function getAvailableSlots(int $doctorId, string $date): array
    {
        $availability = DoctorAvailability::query()
            ->forDoctor($doctorId)
            ->active()
            ->where('date', $date)
            ->first();

        if (!$availability) {
            return [];
        }

        return $availability->getAvailableSlots();
    }

    public function getByDoctorForDateRange(int $doctorId, $startDate, $endDate): Collection
    {
        return DoctorAvailability::query()
            ->forDoctor($doctorId)
            ->forDateRange($startDate, $endDate)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }

    public function deleteUnbookedByDoctorAndDate(int $doctorId, $date): int
    {
        return DoctorAvailability::query()
            ->forDoctor($doctorId)
            ->where('date', $date)
            ->where('is_recurring_generated', true)
            ->whereDoesntHave('bookings', function ($query) {
                $query->whereIn('status', [
                    \Modules\Booking\Enums\BookingStatusEnum::PENDING->value,
                    \Modules\Booking\Enums\BookingStatusEnum::CONFIRMED->value,
                ]);
            })
            ->delete();
    }

    public function deleteFutureUnbookedByScheduleId(int $scheduleId): int
    {
        return DoctorAvailability::query()
            ->where('recurring_schedule_id', $scheduleId)
            ->where('date', '>=', now()->toDateString())
            ->whereDoesntHave('bookings', function ($query) {
                $query->whereIn('status', [
                    \Modules\Booking\Enums\BookingStatusEnum::PENDING->value,
                    \Modules\Booking\Enums\BookingStatusEnum::CONFIRMED->value,
                ]);
            })
            ->delete();
    }

    public function storeOrUpdateFromRecurring(array $data): DoctorAvailability
    {
        return DoctorAvailability::updateOrCreate(
            [
                'doctor_id' => $data['doctor_id'],
                'date' => $data['date'],
                'start_time' => $data['start_time'],
                'recurring_schedule_id' => $data['recurring_schedule_id'],
            ],
            $data
        );
    }
}
