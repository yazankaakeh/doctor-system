<?php

namespace Modules\Doctor\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Doctor\Models\Medicine;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $endocrinology_medicines = [
            // Diabetes - Oral
            'Metformin',
            'Glipizide',
            'Glyburide',
            'Glimepiride',
            'Pioglitazone',
            'Sitagliptin',
            'Linagliptin',
            'Saxagliptin',
            'Alogliptin',
            'Canagliflozin',
            'Dapagliflozin',
            'Empagliflozin',
            'Ertugliflozin',
            'Repaglinide',
            'Nateglinide',
            'Acarbose',

            // Diabetes - Injectable (non-insulin)
            'Exenatide',
            'Liraglutide',
            'Dulaglutide',
            'Semaglutide',
            'Pramlintide',

            // Insulin Preparations
            'Insulin Lispro',
            'Insulin Aspart',
            'Insulin Glulisine',
            'Regular Insulin',
            'NPH Insulin',
            'Insulin Detemir',
            'Insulin Glargine',
            'Insulin Degludec',

            // Thyroid Disorders
            'Levothyroxine',
            'Liothyronine',
            'Methimazole',
            'Propylthiouracil',
            'Potassium Iodide',

            // Adrenal Disorders
            'Hydrocortisone',
            'Prednisone',
            'Dexamethasone',
            'Fludrocortisone',
            'Metyrapone',
            'Ketoconazole', // in Cushing’s
            'Mitotane',

            // Osteoporosis / Calcium Disorders
            'Alendronate',
            'Risedronate',
            'Ibandronate',
            'Zoledronic Acid',
            'Denosumab',
            'Raloxifene',
            'Calcitonin',
            'Teriparatide',
            'Calcium Carbonate',
            'Calcium Citrate',
            'Vitamin D (Cholecalciferol, Ergocalciferol)',
            'Calcitriol',

            // Others
            'Octreotide',
            'Cabergoline',
            'Bromocriptine',
            'Desmopressin',
        ];
        foreach ($endocrinology_medicines as $medicine) {
            Medicine::query()->create(['name' => $medicine]);
        }
    }
}
