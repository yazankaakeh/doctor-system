<?php

namespace Modules\Auth\Actions\Patient;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Concerns\ThrottlesAuthAttempts;

class LoginAction
{
    use ThrottlesAuthAttempts;

    /**
     * Auth guard this action drives.
     */
    private const GUARD = 'web';

    /**
     * Handle the login action for patients.
     *
     * @throws ValidationException
     */
    public function handle(array $credentials, bool $remember = false): bool
    {
        $request = request();
        $email = $credentials['email'];

        // 1. Reject up-front if this (email + IP + guard) bucket is already locked.
        $this->ensureIsNotRateLimited($email, $request, self::GUARD);

        $authenticated = Auth::guard(self::GUARD)->attempt(
            [
                'email' => $email,
                'password' => $credentials['password'],
            ],
            $remember
        );

        if (! $authenticated) {
            // 2. Count this failed attempt against the limiter.
            $this->hitRateLimiter($email, $request, self::GUARD);

            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        // 3. Successful login — wipe the limiter so the user doesn't carry
        //    previous failed attempts forward.
        $this->clearRateLimiter($email, $request, self::GUARD);

        // Regenerate session to prevent session fixation
        $request->session()->regenerate();

        return true;
    }
}
