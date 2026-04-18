<?php

namespace Modules\Doctor\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Core\App\Enums\Gender;
use Modules\Core\app\Models\Country;
use Modules\Doctor\Enums\BloodType;
use Modules\Doctor\Enums\MaritalStatus;
use Modules\Doctor\Models\Patient;

class PatientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Patient::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'age' => $this->faker->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
            'gender' => $this->faker->randomElement(Gender::cases()),
            'children' => $this->faker->numberBetween(0, 6),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->email(),
            'work' => $this->faker->jobTitle(),
            'blood_type' => $this->faker->randomElement(BloodType::cases()), // enum
            'marital_status' => $this->faker->randomElement(MaritalStatus::cases()), // enum
            'drug_allergies' => $this->faker->sentence(3),
            'disabilities' => $this->faker->sentence(3),
            'medical_history' => $this->faker->sentence(3),
            'surgical_history' => $this->faker->sentence(3),
            'accident_history' => $this->faker->sentence(3),
            'password' => 'password', // will be hashed by the cast
            'nationality_id' => null,
            'is_active' => $this->faker->randomElement(ActiveEnum::cases()), // enum
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => ActiveEnum::INACTIVE,
        ]);
    }

    /**
     * Configure the factory to add a random avatar after creation.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Patient $patient) {
            $avatarNumber = rand(1, 15);
            $avatarPath = public_path("assets/img/avatars/{$avatarNumber}.png");

            if (file_exists($avatarPath)) {
                $patient->addMedia($avatarPath)
                    ->preservingOriginal()
                    ->toMediaCollection('default');
            }
        });
    }
}
