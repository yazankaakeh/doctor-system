<?php

namespace Modules\Booking\Repository\ScheduleException;

use Illuminate\Support\Collection;
use Modules\Booking\Models\DoctorScheduleException;

class ScheduleExceptionRepository implements ScheduleExceptionInterface
{
    public function __construct(
        protected DoctorScheduleException $model
    ) {}

    public function getByDoctor(int $doctorId): Collection
    {
        return $this->model
            ->forDoctor($doctorId)
            ->with('recurringSchedule')
            ->orderBy('exception_date')
            ->get();
    }

    public function getByDoctorForDateRange(int $doctorId, $startDate, $endDate): Collection
    {
        return $this->model
            ->forDoctor($doctorId)
            ->forDateRange($startDate, $endDate)
            ->with('recurringSchedule')
            ->orderBy('exception_date')
            ->get();
    }

    public function create(array $data): DoctorScheduleException
    {
        return $this->model->create($data);
    }

    public function delete(int $id): bool
    {
        $exception = $this->find($id);

        return $exception->delete();
    }

    public function find(int $id): DoctorScheduleException
    {
        return $this->model->findOrFail($id);
    }

    public function hasExceptionOnDate(int $doctorId, $date, ?int $scheduleId = null): bool
    {
        $query = $this->model
            ->forDoctor($doctorId)
            ->forDate($date);

        if ($scheduleId !== null) {
            $query->where('recurring_schedule_id', $scheduleId);
        }

        return $query->exists();
    }

    public function getExceptionForDate(int $doctorId, $date, ?int $scheduleId = null): ?DoctorScheduleException
    {
        $query = $this->model
            ->forDoctor($doctorId)
            ->forDate($date);

        if ($scheduleId !== null) {
            $query->where('recurring_schedule_id', $scheduleId);
        }

        return $query->first();
    }
}
