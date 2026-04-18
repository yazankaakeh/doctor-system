<?php

namespace Modules\Doctor\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Doctor\Models\Clinic;

class ClinicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clinics = [
            [
                'name' => [
                    'en' => 'Main Medical Center',
                    'ar' => 'المركز الطبي الرئيسي',
                    'tr' => 'Ana Tıp Merkezi',
                ],
            ],
            [
                'name' => [
                    'en' => 'Family Care Clinic',
                    'ar' => 'عيادة رعاية الأسرة',
                    'tr' => 'Aile Bakım Kliniği',
                ],
            ],
            [
                'name' => [
                    'en' => 'Pediatric Health Center',
                    'ar' => 'مركز صحة الأطفال',
                    'tr' => 'Çocuk Sağlığı Merkezi',
                ],
            ],
            [
                'name' => [
                    'en' => 'Cardiology Clinic',
                    'ar' => 'عيادة أمراض القلب',
                    'tr' => 'Kardiyoloji Kliniği',
                ],
            ],
            [
                'name' => [
                    'en' => 'Dermatology Center',
                    'ar' => 'مركز الأمراض الجلدية',
                    'tr' => 'Dermatoloji Merkezi',
                ],
            ],
        ];

        foreach ($clinics as $clinic) {
            Clinic::query()->updateOrCreate(
                ['name->en' => $clinic['name']['en']],
                [
                    'name' => $clinic['name'],
                    'is_active' => ActiveEnum::ACTIVE->value,
                ]
            );
        }

        $this->command->info('Clinics seeded successfully!');
    }
}
