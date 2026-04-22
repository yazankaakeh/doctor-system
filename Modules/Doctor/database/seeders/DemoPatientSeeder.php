<?php

namespace Modules\Doctor\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Core\App\Enums\Gender;
use Modules\Doctor\Enums\BloodType;
use Modules\Doctor\Enums\MaritalStatus;
use Modules\Doctor\Models\Clinic;
use Modules\Doctor\Models\Patient;

/**
 * Seeds a single deterministic demo patient so the login page's auto-fill
 * "Patient" credential (config/demo.php) resolves to a real account right
 * after `php artisan migrate:fresh --seed`.
 *
 * The password mirrors the DoctorSeeder convention — a strong default that
 * can be overridden per environment via .env so the repo never ships a
 * weak or guessable credential:
 *
 *   DEMO_PATIENT_PASSWORD=YourStrongPassword@2026
 */
class DemoPatientSeeder extends Seeder
{
    /**
     * Strong default password for the seeded demo patient.
     */
    private const DEFAULT_PATIENT_PASSWORD = 'Patient@2026!';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var Patient $patient */
        $patient = Patient::query()->updateOrCreate(
            [
                'email' => 'patient@demo.com',
            ],
            [
                'name' => 'Demo Patient',
                'age' => 30,
                'gender' => Gender::MALE->value,
                'children' => '0',
                'phone' => '5000000000',
                'email_verified_at' => now(),
                'work' => 'Software Engineer',
                'blood_type' => BloodType::O_POSITIVE->value,
                'marital_status' => MaritalStatus::SINGLE->value,
                'drug_allergies' => 'None',
                'disabilities' => null,
                'medical_history' => 'No significant medical history.',
                'surgical_history' => null,
                'accident_history' => null,
                'is_active' => ActiveEnum::ACTIVE->value,
                'password' => Hash::make(
                    env('DEMO_PATIENT_PASSWORD', self::DEFAULT_PATIENT_PASSWORD)
                ),
            ]
        );

        // Attach the demo patient to the first available clinic so the
        // booking flow has something to hook into out of the box. Clinics
        // are seeded earlier in DoctorDatabaseSeeder by ClinicSeeder.
        $clinicId = Clinic::query()->orderBy('id')->value('id');
        if ($clinicId && ! $patient->clinics()->where('clinics.id', $clinicId)->exists()) {
            $patient->clinics()->attach($clinicId);
        }

        $this->command->info('Demo patient seeder completed successfully!');
    }
}
