<?php

namespace Modules\Booking\Actions\RecurringSchedule;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\DoctorRecurringSchedule;
use Modules\Booking\Repository\RecurringSchedule\RecurringScheduleInterface;

class CreateRecurringScheduleAction
{
    /**
     * Rolling window (in days) of availability rows we materialize
     * from the recurring schedule on create. The console command
     * booking:generate-availabilities can extend this further.
     */
    protected const GENERATE_WINDOW_DAYS = 30;

    public function __construct(
        protected RecurringScheduleInterface $repository,
        protected GenerateAvailabilitiesFromScheduleAction $generator,
    ) {}

    public function handle(array $data): DoctorRecurringSchedule
    {
        $schedule = $this->repository->create($data);

        // Immediately materialize concrete availability rows for the
        // next N days so the patient booking wizard has slots to show.
        // Failures here must NOT roll back schedule creation — log and
        // let the scheduled command backfill later.
        try {
            $startDate = Carbon::today();
            $endDate = Carbon::today()->addDays(self::GENERATE_WINDOW_DAYS);

            $this->generator->handle(
                (int) $schedule->doctor_id,
                $startDate,
                $endDate,
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to auto-generate availabilities from recurring schedule', [
                'schedule_id' => $schedule->id,
                'doctor_id' => $schedule->doctor_id,
                'error' => $e->getMessage(),
            ]);
        }

        return $schedule;
    }
}
