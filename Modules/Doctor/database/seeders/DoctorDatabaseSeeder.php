<?php

namespace Modules\Doctor\Database\Seeders;

use Illuminate\Database\Seeder;

class DoctorDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            MedicineSeeder::class,
            MedicalTestsSeeder::class,
            VitalSignsSeeder::class,
            ClinicSeeder::class,
            FinalDiagnosisSeeder::class,
            DosageFormSeeder::class,
            // Keep PatientSeeder LAST — it depends on at least one Clinic
            // existing (attaches each patient to 1–3 clinics).
            PatientSeeder::class,
        ]);
    }
}
