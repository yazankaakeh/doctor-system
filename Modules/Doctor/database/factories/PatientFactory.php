<?php

namespace Modules\Doctor\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Core\App\Enums\Gender;
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
     *
     * Notes:
     * - `age` is stored as an integer (see fix_patients_age_column_type).
     * - `phone` has a unique index, so we must use ->unique() to avoid
     *   collisions across large batch seeds.
     * - `password` is auto-hashed by the model's `password => hashed` cast.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 85),
            'gender' => $this->faker->randomElement(Gender::cases()),
            'children' => (string) $this->faker->numberBetween(0, 6),
            // Bounded, unique, digits-only phone — avoids the 20+ char
            // formatted strings `phoneNumber()` can emit and guarantees
            // uniqueness over large seeds.
            'phone' => $this->faker->unique()->numerify('5#########'),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'work' => $this->faker->jobTitle(),
            'blood_type' => $this->faker->randomElement(BloodType::cases()),
            'marital_status' => $this->faker->randomElement(MaritalStatus::cases()),
            'drug_allergies' => $this->faker->sentence(4),
            'disabilities' => $this->faker->optional(0.2)->sentence(4),
            'medical_history' => $this->faker->sentence(6),
            'surgical_history' => $this->faker->optional(0.3)->sentence(5),
            'accident_history' => $this->faker->optional(0.2)->sentence(5),
            // Plain string — model cast hashes it automatically.
            'password' => 'Password123!',
            'nationality_id' => null,
            // 90% active / 10% inactive — more realistic than 50/50.
            'is_active' => $this->faker->boolean(90) ? ActiveEnum::ACTIVE : ActiveEnum::INACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => ActiveEnum::INACTIVE,
        ]);
    }

    /**
     * Skip the avatar attachment. Use this for fast bulk seeding — adding
     * media files via Spatie MediaLibrary multiplies insert cost several times.
     */
    public function withoutAvatar(): static
    {
        return $this->afterCreating(fn () => null)
            ->state([]);
    }

    /**
     * Configure the factory to add a random avatar after creation.
     *
     * This only runs when the avatar file exists on disk; we silently skip
     * otherwise so seeds don't explode on a fresh checkout.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Patient $patient) {
            $avatarNumber = random_int(1, 15);
            $avatarPath = public_path("assets/img/avatars/{$avatarNumber}.png");

            if (file_exists($avatarPath)) {
                $patient->addMedia($avatarPath)
                    ->preservingOriginal()
                    ->toMediaCollection('default');
            }
        });
    }
}
