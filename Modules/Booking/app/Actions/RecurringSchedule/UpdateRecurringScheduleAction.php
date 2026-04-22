<?php

namespace Modules\Booking\Actions\RecurringSchedule;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\DoctorRecurringSchedule;
use Modules\Booking\Repository\Availability\AvailabilityInterface;
use Modules\Booking\Repository\RecurringSchedule\RecurringScheduleInterface;

class UpdateRecurringScheduleAction
{
    /**
     * Rolling window (in days) of availability rows we regenerate
     * on update. Matches the create action.
     */
    protected const GENERATE_WINDOW_DAYS = 30;

    public function __construct(
        protected RecurringScheduleInterface $repository,
        protected AvailabilityInterface $availabilityRepository,
        protected GenerateAvailabilitiesFromScheduleAction $generator,
    ) {}

    public function handle(int $id, array $data): DoctorRecurringSchedule
    {
        $schedule = $this->repository->update($id, $data);

        // If schedule was deactivated, clean up future unbooked slots
        // and stop — we don't regenerate for an inactive schedule.
        if (isset($data['is_active']) && ! $data['is_active']) {
            $this->availabilityRepository->deleteFutureUnbookedByScheduleId($id);

            return $schedule;
        }

        // Schedule is active (or activation state wasn't touched). Any
        // time/day/fee change means existing future slots are stale.
        // Drop future unbooked rows tied to this schedule and
        // regenerate from the current schedule values.
        try {
            $this->availabilityRepository->deleteFutureUnbookedByScheduleId($id);

            $startDate = Carbon::today();
            $endDate = Carbon::today()->addDays(self::GENERATE_WINDOW_DAYS);

            $this->generator->handle(
                (int) $schedule->doctor_id,
                $startDate,
                $endDate,
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to regenerate availabilities after schedule update', [
                'schedule_id' => $schedule->id,
                'doctor_id' => $schedule->doctor_id,
                'error' => $e->getMessage(),
            ]);
        }

        return $schedule;
    }
}
