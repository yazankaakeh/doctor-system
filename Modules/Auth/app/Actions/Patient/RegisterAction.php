<?php

namespace Modules\Auth\Actions\Patient;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Doctor\Models\Patient;

class RegisterAction
{
    /**
     * Handle the registration action for patients.
     */
    public function handle(array $data): Patient
    {
        $patient = Patient::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'age' => $data['age'] ?? null,
            'gender' => $data['gender'] ?? null,
            'blood_type' => $data['blood_type'] ?? null,
            'marital_status' => $data['marital_status'] ?? null,
            'work' => $data['work'] ?? null,
            'nationality_id' => $data['nationality_id'] ?? null,
            'is_active' => 1,
        ]);

        // Fire the registered event for email verification
        event(new Registered($patient));

        // Log the patient in
        Auth::guard('web')->login($patient);

        // Regenerate session for security
        session()->regenerate();

        return $patient;
    }
}
