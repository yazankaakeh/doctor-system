<?php

namespace Modules\Auth\Actions\Doctor;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Doctor\Models\Doctor;

class RegisterAction
{
    /**
     * Handle the registration action for doctors.
     * New doctors are set to INACTIVE (pending approval).
     */
    public function handle(array $data): Doctor
    {
        $doctor = Doctor::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'gender' => $data['gender'] ?? null,
            'age' => $data['age'] ?? null,
            'medical_specialty_id' => $data['medical_specialty_id'],
            'is_active' => ActiveEnum::INACTIVE, // Pending approval
        ]);

        // Fire the registered event for email verification
        event(new Registered($doctor));

        return $doctor;
    }
}
