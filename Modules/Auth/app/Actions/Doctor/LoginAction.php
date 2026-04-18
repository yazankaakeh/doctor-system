<?php

namespace Modules\Auth\Actions\Doctor;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Doctor\Models\Doctor;

class LoginAction
{
    /**
     * Handle the login action for doctors.
     *
     * @throws ValidationException
     */
    public function handle(array $credentials, bool $remember = false): bool
    {
        // First check if doctor exists and is active
        $doctor = Doctor::query()->where('email', $credentials['email'])->first();

        if ($doctor && $doctor->is_active === ActiveEnum::INACTIVE) {
            throw ValidationException::withMessages([
                'email' => trans('auth::auth.account_pending_approval'),
            ]);
        }

        $authenticated = Auth::guard('doctor')->attempt(
            [
                'email' => $credentials['email'],
                'password' => $credentials['password'],
                'is_active' => ActiveEnum::ACTIVE, // Only allow active doctors
            ],
            $remember
        );

        if (! $authenticated) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        // Regenerate session to prevent session fixation
        request()->session()->regenerate();

        return true;
    }
}
