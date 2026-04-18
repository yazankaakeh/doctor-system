<?php

namespace Modules\Booking\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Booking\Actions\RecurringSchedule\GenerateAvailabilitiesFromScheduleAction;
use Modules\Doctor\Models\Doctor;

class GenerateRecurringAvailabilitiesCommand extends Command
{
    protected $signature = 'booking:generate-availabilities
                            {--days=14 : Number of days to generate availabilities for}
                            {--doctor= : Specific doctor ID to generate for (optional)}';

    protected $description = 'Generate availability slots from recurring schedules for all active doctors';

    public function handle(GenerateAvailabilitiesFromScheduleAction $action): int
    {
        $days = (int) $this->option('days');
        $doctorId = $this->option('doctor');

        if ($days < 1 || $days > 90) {
            $this->error('Days must be between 1 and 90.');

            return self::FAILURE;
        }

        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays($days);

        $this->info("Generating availabilities from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}...");

        $query = Doctor::query()
            ->whereHas('recurringSchedules', function ($q) {
                $q->where('is_active', true);
            });

        if ($doctorId) {
            $query->where('id', $doctorId);
        }

        $doctors = $query->get();

        if ($doctors->isEmpty()) {
            $this->warn('No doctors with active recurring schedules found.');

            return self::SUCCESS;
        }

        $totalCreated = 0;
        $totalSkipped = 0;

        $this->withProgressBar($doctors, function (Doctor $doctor) use ($action, $startDate, $endDate, &$totalCreated, &$totalSkipped) {
            $result = $action->handle($doctor->id, $startDate, $endDate);
            $totalCreated += $result['created'];
            $totalSkipped += $result['skipped'];
        });

        $this->newLine(2);
        $this->info('Generation complete!');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Doctors processed', $doctors->count()],
                ['Slots created', $totalCreated],
                ['Slots skipped (exceptions)', $totalSkipped],
            ]
        );

        return self::SUCCESS;
    }
}
