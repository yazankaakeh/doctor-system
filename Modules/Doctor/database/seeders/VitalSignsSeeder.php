<?php

namespace Modules\Doctor\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Doctor\Models\VitalSign;

class VitalSignsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vitalSigns = [
            [
                'name' => ['en' => 'Blood Pressure (Systolic)', 'ar' => 'ضغط الدم (الانقباضي)', 'tr' => 'Kan Basıncı (Sistolik)'],
                'min_value' => 90,
                'max_value' => 120,
                'unit' => 'mmHg',
            ],
            [
                'name' => ['en' => 'Blood Pressure (Diastolic)', 'ar' => 'ضغط الدم (الانبساطي)', 'tr' => 'Kan Basıncı (Diyastolik)'],
                'min_value' => 60,
                'max_value' => 80,
                'unit' => 'mmHg',
            ],
            [
                'name' => ['en' => 'Heart Rate', 'ar' => 'معدل ضربات القلب', 'tr' => 'Kalp Atış Hızı'],
                'min_value' => 60,
                'max_value' => 100,
                'unit' => 'bpm',
            ],
            [
                'name' => ['en' => 'Temperature', 'ar' => 'درجة الحرارة', 'tr' => 'Vücut Sıcaklığı'],
                'min_value' => 36.1,
                'max_value' => 37.2,
                'unit' => '°C',
            ],
            [
                'name' => ['en' => 'Weight', 'ar' => 'الوزن', 'tr' => 'Ağırlık'],
                'min_value' => null,
                'max_value' => null,
                'unit' => 'kg',
            ],
            [
                'name' => ['en' => 'Height', 'ar' => 'الطول', 'tr' => 'Boy'],
                'min_value' => null,
                'max_value' => null,
                'unit' => 'cm',
            ],
            [
                'name' => ['en' => 'Waist Circumference', 'ar' => 'محيط الخصر', 'tr' => 'Bel Çevresi'],
                'min_value' => null,
                'max_value' => 102,
                'unit' => 'cm',
            ],
            [
                'name' => ['en' => 'Body Mass Index (BMI)', 'ar' => 'مؤشر كتلة الجسم', 'tr' => 'Vücut Kitle İndeksi'],
                'min_value' => 18.5,
                'max_value' => 24.9,
                'unit' => 'kg/m²',
            ],
            [
                'name' => ['en' => 'Respiratory Rate', 'ar' => 'معدل التنفس', 'tr' => 'Solunum Hızı'],
                'min_value' => 12,
                'max_value' => 20,
                'unit' => 'breaths/min',
            ],
            [
                'name' => ['en' => 'Oxygen Saturation (SpO₂)', 'ar' => 'تشبع الأكسجين', 'tr' => 'Oksijen Satürasyonu'],
                'min_value' => 95,
                'max_value' => 100,
                'unit' => '%',
            ],
            [
                'name' => ['en' => 'Peak Expiratory Flow (PEF)', 'ar' => 'ذروة التدفق الزفيري', 'tr' => 'Tepe Ekspiratuar Akış'],
                'min_value' => 400,
                'max_value' => 700,
                'unit' => 'L/min',
            ],
            [
                'name' => ['en' => 'Blood Glucose (Fasting)', 'ar' => 'سكر الدم (صائم)', 'tr' => 'Kan Şekeri (Açlık)'],
                'min_value' => 70,
                'max_value' => 100,
                'unit' => 'mg/dL',
            ],
            [
                'name' => ['en' => 'Blood Glucose (Random)', 'ar' => 'سكر الدم (عشوائي)', 'tr' => 'Kan Şekeri (Rastgele)'],
                'min_value' => 70,
                'max_value' => 140,
                'unit' => 'mg/dL',
            ],
        ];

        foreach ($vitalSigns as $vitalSign) {
            VitalSign::query()->updateOrCreate(
                ['name->en' => $vitalSign['name']['en']],
                [
                    'name' => $vitalSign['name'],
                    'min_value' => $vitalSign['min_value'],
                    'max_value' => $vitalSign['max_value'],
                    'unit' => $vitalSign['unit'],
                ]
            );
        }

        $this->command->info('Vital signs seeded successfully!');
    }
}
