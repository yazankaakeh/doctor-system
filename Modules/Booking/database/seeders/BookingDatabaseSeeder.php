<?php

namespace Modules\Booking\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Booking\Models\DoctorAvailability;
use Modules\Doctor\Models\Doctor;

class BookingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = Doctor::all();

        if ($doctors->isEmpty()) {
            $this->command->warn('No doctors found. Please seed doctors first.');

            return;
        }

        $this->command->info('Creating availability slots for '.$doctors->count().' doctors...');

        foreach ($doctors as $doctor) {
            // Create availability for the next 14 days
            for ($i = 1; $i <= 14; $i++) {
                $date = Carbon::today()->addDays($i);

                // Skip weekends optionally
                if ($date->isWeekend()) {
                    continue;
                }

                // Morning slot: 9:00 - 12:00
                DoctorAvailability::create([
                    'doctor_id' => $doctor->id,
                    'date' => $date->format('Y-m-d'),
                    'start_time' => '09:00',
                    'end_time' => '12:00',
                    'slot_duration' => 30,
                    'consultation_fee' => rand(50, 150),
                    'is_active' => true,
                ]);

                // Afternoon slot: 14:00 - 17:00
                DoctorAvailability::create([
                    'doctor_id' => $doctor->id,
                    'date' => $date->format('Y-m-d'),
                    'start_time' => '14:00',
                    'end_time' => '17:00',
                    'slot_duration' => 30,
                    'consultation_fee' => rand(50, 150),
                    'is_active' => true,
                ]);
            }

            $this->command->info("Created availability for Dr. {$doctor->name}");
        }

        $this->command->info('Booking seeder completed!');
    }
}
