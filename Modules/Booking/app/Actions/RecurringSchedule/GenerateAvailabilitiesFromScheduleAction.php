<?php

namespace Modules\Booking\Actions\RecurringSchedule;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Models\DoctorAvailability;
use Modules\Booking\Repository\Availability\AvailabilityInterface;
use Modules\Booking\Repository\RecurringSchedule\RecurringScheduleInterface;
use Modules\Booking\Repository\ScheduleException\ScheduleExceptionInterface;

class GenerateAvailabilitiesFromScheduleAction
{
    public function __construct(
        protected RecurringScheduleInterface $scheduleRepository,
        protected ScheduleExceptionInterface $exceptionRepository,
        protected AvailabilityInterface $availabilityRepository
    ) {}

    public function handle(int $doctorId, Carbon $startDate, Carbon $endDate): array
    {
        $schedules = $this->scheduleRepository->getEffectiveSchedulesForDateRange(
            $doctorId,
            $startDate,
            $endDate
        );

        $exceptions = $this->exceptionRepository->getByDoctorForDateRange(
            $doctorId,
            $startDate,
            $endDate
        );

        $exceptionMap = [];
        foreach ($exceptions as $exception) {
            $key = $exception->exception_date->format('Y-m-d') . '_' . ($exception->recurring_schedule_id ?? 'all');
            $exceptionMap[$key] = $exception;
        }

        $period = CarbonPeriod::create($startDate, $endDate);
        $createdCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($period as $date) {
                $dayOfWeek = $date->dayOfWeek;

                foreach ($schedules as $schedule) {
                    if ($schedule->day_of_week !== $dayOfWeek) {
                        continue;
                    }

                    if (!$schedule->isEffectiveOn($date)) {
                        continue;
                    }

                    // Check for specific schedule exception
                    $specificKey = $date->format('Y-m-d') . '_' . $schedule->id;
                    $globalKey = $date->format('Y-m-d') . '_all';

                    $exception = $exceptionMap[$specificKey] ?? $exceptionMap[$globalKey] ?? null;

                    if ($exception) {
                        if ($exception->isSkip()) {
                            $skippedCount++;
                            continue;
                        }

                        if ($exception->isModified()) {
                            // Create availability with modified times
                            $this->createAvailability(
                                $doctorId,
                                $date,
                                $exception->alternate_start_time->format('H:i'),
                                $exception->alternate_end_time->format('H:i'),
                                $schedule->slot_duration,
                                $schedule->consultation_fee,
                                $schedule->id
                            );
                            $createdCount++;
                            continue;
                        }
                    }

                    // Create normal availability from schedule
                    $this->createAvailability(
                        $doctorId,
                        $date,
                        $schedule->start_time->format('H:i'),
                        $schedule->end_time->format('H:i'),
                        $schedule->slot_duration,
                        $schedule->consultation_fee,
                        $schedule->id
                    );
                    $createdCount++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'created' => $createdCount,
            'skipped' => $skippedCount,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ];
    }

    protected function createAvailability(
        int $doctorId,
        Carbon $date,
        string $startTime,
        string $endTime,
        int $slotDuration,
        float $consultationFee,
        int $scheduleId
    ): DoctorAvailability {
        return $this->availabilityRepository->storeOrUpdateFromRecurring([
            'doctor_id' => $doctorId,
            'date' => $date->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'slot_duration' => $slotDuration,
            'consultation_fee' => $consultationFee,
            'is_active' => true,
            'recurring_schedule_id' => $scheduleId,
            'is_recurring_generated' => true,
        ]);
    }
}
