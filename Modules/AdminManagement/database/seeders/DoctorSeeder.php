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

        // Seed two additional demo doctors with different specialties so the
        // public landing page's "Meet Our Doctors" section has 4 real doctors
        // (with their specialty as the role) instead of placeholder cards.
        $this->seedAdditionalDemoDoctors();

        $this->command->info('Doctor seeder completed successfully!');
    }

    /**
     * Create/refresh two extra demo doctors so the landing page can render
     * 4 real doctors out of the box. Kept separate from the main doctors so
     * their existing credentials are never touched.
     */
    private function seedAdditionalDemoDoctors(): void
    {
        $extras = [
            [
                'email' => 'doctor.cardio@demo.com',
                'name' => 'Dr. Omar Khan',
                'gender' => Gender::MALE->value,
                'age' => 42,
                'phone' => '1122334455',
                'specialty_code' => 'CARDIOLOGY',
                'specialty_name' => [
                    'en' => 'Cardiology',
                    'ar' => 'أمراض القلب',
                ],
                'password_env' => 'DEMO_DOCTOR3_PASSWORD',
                'password_default' => 'Doctor3@2026!',
                'bio' => '15+ years of experience in cardiovascular medicine.',
            ],
            [
                'email' => 'doctor.pediatrics@demo.com',
                'name' => 'Dr. Layla Al-Mansour',
                'gender' => Gender::FEMALE->value,
                'age' => 38,
                'phone' => '2233445566',
                'specialty_code' => 'PEDIATRICS',
                'specialty_name' => [
                    'en' => 'Pediatrics',
                    'ar' => 'طب الأطفال',
                ],
                'password_env' => 'DEMO_DOCTOR4_PASSWORD',
                'password_default' => 'Doctor4@2026!',
                'bio' => 'Dedicated to providing compassionate care for children.',
            ],
        ];

        foreach ($extras as $extra) {
            /** @var MedicalSpecialty $specialty */
            $specialty = MedicalSpecialty::query()->firstOrCreate(
                ['code' => $extra['specialty_code']],
                ['name' => $extra['specialty_name'], 'is_active' => 1]
            );

            /** @var Doctor $doctor */
            $doctor = Doctor::query()->updateOrCreate(
                ['email' => $extra['email']],
                [
                    'name' => $extra['name'],
                    'gender' => $extra['gender'],
                    'medical_specialty_id' => $specialty->id,
                    'age' => $extra['age'],
                    'is_active' => 1,
                    'phone' => $extra['phone'],
                    'bio' => $extra['bio'],
                    'password' => Hash::make(
                        env($extra['password_env'], $extra['password_default'])
                    ),
                ]
            );

            if (! $doctor->hasRole(Roles::SUPER_ADMIN->value)) {
                $doctor->assignRole(Roles::SUPER_ADMIN->value);
            }
        }
    }
}
