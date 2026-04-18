<?php

namespace Modules\Doctor\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Doctor\Models\MedicalSpecialty;

class MedicalSpecialtyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = MedicalSpecialty::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'code' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
        ];
    }
}
