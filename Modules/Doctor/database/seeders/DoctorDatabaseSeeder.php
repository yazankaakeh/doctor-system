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
            // PatientSeeder ::class,
            ClinicSeeder::class,
            FinalDiagnosisSeeder::class,
            DosageFormSeeder::class,
        ]);
    }
}
