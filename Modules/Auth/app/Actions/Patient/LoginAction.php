<?php

namespace Modules\Auth\Actions\Patient;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    /**
     * Handle the login action for patients.
     *
     * @throws ValidationException
     */
    public function handle(array $credentials, bool $remember = false): bool
    {
        $authenticated = Auth::guard('web')->attempt(
            [
                'email' => $credentials['email'],
                'password' => $credentials['password'],
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
