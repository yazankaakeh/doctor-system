<?php

namespace Modules\AdminManagement\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\AdminManagement\Enums\Roles;
use Modules\Core\App\Enums\Gender;
use Modules\Doctor\Enums\MedicalSpecialtyCodeEnum;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\MedicalSpecialty;
use Spatie\Permission\Models\Role;

class DoctorSeeder extends Seeder
{
    /**
     * Strong default passwords for seeded demo accounts.
     *
     * They can be overridden per-environment via .env so the repository
     * never ships a weak / guessable credential.
     *
     *   DEMO_DOCTOR_PASSWORD=YourStrongPassword@2026
     *   DEMO_DOCTOR2_PASSWORD=YourStrongPassword@2026
     */
    private const DEFAULT_DOCTOR_PASSWORD = 'Doctor@2026!';

    private const DEFAULT_DOCTOR2_PASSWORD = 'Doctor2@2026!';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or get the SUPER_ADMIN role for doctor guard
        $role = Role::firstOrCreate(
            [
                'name' => Roles::SUPER_ADMIN->value,
                'guard_name' => 'doctor',
            ]
        );

        // Create or get the medical specialty
        $names = [
            'en' => 'Internal Medicine And Endocrinology',
            'ar' => 'الطب الباطني والغدد الصماء',
        ];

        /** @var MedicalSpecialty $medical */
        $medical = MedicalSpecialty::query()->firstOrCreate(
            [
                'code' => MedicalSpecialtyCodeEnum::INTERNAL_MEDICINE_AND_ENDOCRINOLOGY,
            ],
            [
                'name' => $names,
            ]
        );

        // Create or update demo doctor 1
        /** @var Doctor $demoDoctor1 */
        $demoDoctor1 = Doctor::query()->updateOrCreate(
            [
                'email' => 'doctor@demo.com',
            ],
            [
                'name' => 'Dr. John Smith',
                'gender' => Gender::MALE->value,
                'medical_specialty_id' => $medical->id,
                'age' => 35,
                'is_active' => 1,
                'phone' => '1234567890',
                'password' => Hash::make(
                    env('DEMO_DOCTOR_PASSWORD', self::DEFAULT_DOCTOR_PASSWORD)
                ),
            ]
        );

        // Create or update demo doctor 2
        /** @var Doctor $demoDoctor2 */
        $demoDoctor2 = Doctor::query()->updateOrCreate(
            [
                'email' => 'doctor2@demo.com',
            ],
            [
                'name' => 'Dr. Sarah Johnson',
                'gender' => Gender::FEMALE->value,
                'medical_specialty_id' => $medical->id,
                'age' => 32,
                'is_active' => 1,
                'phone' => '0987654321',
                'password' => Hash::make(
                    env('DEMO_DOCTOR2_PASSWORD', self::DEFAULT_DOCTOR2_PASSWORD)
                ),
            ]
        );

        // Assign role if not already assigned
        if (! $demoDoctor1->hasRole(Roles::SUPER_ADMIN->value)) {
            $demoDoctor1->assignRole(Roles::SUPER_ADMIN->value);
        }

        if (! $demoDoctor2->hasRole(Roles::SUPER_ADMIN->value)) {
            $demoDoctor2->assignRole(Roles::SUPER_ADMIN->value);
        }

        $this->command->info('Doctor seeder completed successfully!');
    }
}
