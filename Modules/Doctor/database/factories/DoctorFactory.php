<?php

namespace Modules\Doctor\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Core\App\Enums\Gender;
use Modules\Doctor\Models\Doctor;

class DoctorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Doctor::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('##########'),
            'age' => $this->faker->numberBetween(25, 65),
            'gender' => $this->faker->randomElement(Gender::cases()),
            'password' => 'password', // will be hashed by the cast
            'is_active' => $this->faker->randomElement(ActiveEnum::cases()), // enum
        ];
    }

    /**
     * Configure the factory to add a random avatar after creation.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Doctor $doctor) {
            $avatarNumber = rand(1, 15);
            $avatarPath = public_path("assets/img/avatars/{$avatarNumber}.png");

            if (file_exists($avatarPath)) {
                $doctor->addMedia($avatarPath)
                    ->preservingOriginal()
                    ->toMediaCollection('default');
            }
        });
    }
}
