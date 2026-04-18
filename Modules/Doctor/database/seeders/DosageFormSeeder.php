<?php

namespace Modules\Doctor\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Doctor\Models\DosageForm;

class DosageFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dosageForms = [
            'oral_tab' => ['en' => 'Oral tablet', 'ar' => 'أقراص فموية'],
            'oral_drops' => ['en' => 'Oral drops', 'ar' => 'قطرات فموية'],
            'sublingual' => ['en' => 'Sublingual', 'ar' => 'تحت اللسان'],
            'sc' => ['en' => 'Subcutaneous (SC)', 'ar' => 'تحت الجلد'],
            'im' => ['en' => 'Intramuscular (IM)', 'ar' => 'عضلي'],
            'iv' => ['en' => 'Intravenous (IV)', 'ar' => 'وريدي'],
            'inhaler' => ['en' => 'Inhaler', 'ar' => 'بخاخ تنفسي'],
            'nasal' => ['en' => 'Nasal drop / spray', 'ar' => 'قطرات/بخاخ أنفي'],
            'syrup' => ['en' => 'Syrup', 'ar' => 'شراب'],
            'eye_drops' => ['en' => 'Eye drops', 'ar' => 'قطرات عينية'],
            'eye_ointment' => ['en' => 'Eye ointment', 'ar' => 'مرهم عيني'],
            'ear_drops' => ['en' => 'Ear drops', 'ar' => 'قطرات أذن'],
            'cream' => ['en' => 'Cream', 'ar' => 'كريم'],
            'ointment' => ['en' => 'Ointment', 'ar' => 'مرهم'],
            'gel' => ['en' => 'Gel', 'ar' => 'جل'],
            'rectal' => ['en' => 'Rectal', 'ar' => 'شرجي'],
            'vaginal' => ['en' => 'Vaginal', 'ar' => 'مهبلي'],
            'patch' => ['en' => 'Patch', 'ar' => 'لاصقة'],
        ];

        foreach ($dosageForms as $dosageForm) {
            DosageForm::query()->create([
                'name' => $dosageForm,
                'is_active' => ActiveEnum::ACTIVE->value,
            ]);
        }
    }
}
