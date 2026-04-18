<?php

namespace Modules\Booking\Repository\RecurringSchedule;

use Illuminate\Support\Collection;
use Modules\Booking\Models\DoctorRecurringSchedule;

class RecurringScheduleRepository implements RecurringScheduleInterface
{
    public function __construct(
        protected DoctorRecurringSchedule $model
    ) {}

    public function getByDoctor(int $doctorId): Collection
    {
        return $this->model
            ->forDoctor($doctorId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    public function getActiveByDoctor(int $doctorId): Collection
    {
        return $this->model
            ->forDoctor($doctorId)
            ->active()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    public function create(array $data): DoctorRecurringSchedule
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): DoctorRecurringSchedule
    {
        $schedule = $this->find($id);
        $schedule->update($data);

        return $schedule->fresh();
    }

    public function delete(int $id): bool
    {
        $schedule = $this->find($id);

        return $schedule->delete();
    }

    public function find(int $id): DoctorRecurringSchedule
    {
        return $this->model->findOrFail($id);
    }

    public function getEffectiveSchedulesForDateRange(int $doctorId, $startDate, $endDate): Collection
    {
        return $this->model
            ->forDoctor($doctorId)
            ->active()
            ->where('effective_from', '<=', $endDate)
            ->where(function ($query) use ($startDate) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $startDate);
            })
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    public function getByDoctorAndDay(int $doctorId, int $dayOfWeek): Collection
    {
        return $this->model
            ->forDoctor($doctorId)
            ->forDayOfWeek($dayOfWeek)
            ->active()
            ->orderBy('start_time')
            ->get();
    }
}
