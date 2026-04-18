<?php

namespace Modules\Doctor\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Doctor\Models\MedicalExamination;

class MedicalExaminationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = MedicalExamination::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [];
    }
}
