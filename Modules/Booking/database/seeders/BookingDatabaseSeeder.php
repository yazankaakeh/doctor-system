<?php

namespace Modules\Booking\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Booking\Actions\RecurringSchedule\GenerateAvailabilitiesFromScheduleAction;
use Modules\Booking\Models\DoctorRecurringSchedule;
use Modules\Doctor\Models\Doctor;

class BookingDatabaseSeeder extends Seeder
{
    /**
     * Days of week to seed a schedule for (0=Sun ... 6=Sat).
     * Default: Monday through Friday.
     */
    private const WORKING_DAYS = [1, 2, 3, 4, 5];

    /**
     * Number of forward days of availability rows to materialize
     * from the recurring schedules.
     */
    private const GENERATE_WINDOW_DAYS = 30;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = Doctor::all();

        if ($doctors->isEmpty()) {
            $this->command?->warn('No doctors found. Skipping BookingDatabaseSeeder.');

            return;
        }

        $this->command?->info('Seeding recurring schedules for '.$doctors->count().' doctors...');

        $effectiveFrom = Carbon::today();
        $totalSchedules = 0;

        foreach ($doctors as $doctor) {
            foreach (self::WORKING_DAYS as $dayOfWeek) {
                DoctorRecurringSchedule::updateOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'day_of_week' => $dayOfWeek,
                        'start_time' => '09:00',
                    ],
                    [
                        'end_time' => '17:00',
                        'slot_duration' => 30,
                        'consultation_fee' => rand(50, 150),
                        'effective_from' => $effectiveFrom->format('Y-m-d'),
                        'effective_until' => null,
                        'is_active' => true,
                    ],
                );
                $totalSchedules++;
            }
        }

        $this->command?->info("Created/updated {$totalSchedules} recurring schedules.");
        $this->command?->info('Materializing availability rows for the next '.self::GENERATE_WINDOW_DAYS.' days...');

        /** @var GenerateAvailabilitiesFromScheduleAction $generator */
        $generator = app(GenerateAvailabilitiesFromScheduleAction::class);

        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(self::GENERATE_WINDOW_DAYS);

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($doctors as $doctor) {
            $result = $generator->handle((int) $doctor->id, $startDate, $endDate);
            $totalCreated += $result['created'] ?? 0;
            $totalSkipped += $result['skipped'] ?? 0;
        }

        $this->command?->info("Availability generation complete: {$totalCreated} created, {$totalSkipped} skipped.");
    }
}
