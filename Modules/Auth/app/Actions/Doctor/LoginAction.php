<?php

namespace Modules\Auth\Actions\Doctor;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Concerns\ThrottlesAuthAttempts;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Doctor\Models\Doctor;

class LoginAction
{
    use ThrottlesAuthAttempts;

    /**
     * Auth guard this action drives.
     */
    private const GUARD = 'doctor';

    /**
     * Handle the login action for doctors.
     *
     * @throws ValidationException
     */
    public function handle(array $credentials, bool $remember = false): bool
    {
        $request = request();
        $email = $credentials['email'];

        // 1. Reject up-front if this (email + IP + guard) bucket is already locked.
        $this->ensureIsNotRateLimited($email, $request, self::GUARD);

        // 2. Pre-check pending approval separately — this is a legitimate
        //    account, not a credential failure, so it shouldn't burn an attempt.
        $doctor = Doctor::query()->where('email', $email)->first();

        if ($doctor && $doctor->is_active === ActiveEnum::INACTIVE) {
            throw ValidationException::withMessages([
                'email' => trans('auth::auth.account_pending_approval'),
            ]);
        }

        $authenticated = Auth::guard(self::GUARD)->attempt(
            [
                'email' => $email,
                'password' => $credentials['password'],
                'is_active' => ActiveEnum::ACTIVE, // Only allow active doctors
            ],
            $remember
        );

        if (! $authenticated) {
            // 3. Count this failed attempt against the limiter.
            $this->hitRateLimiter($email, $request, self::GUARD);

            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        // 4. Successful login — wipe the limiter so the user doesn't carry
        //    previous failed attempts forward.
        $this->clearRateLimiter($email, $request, self::GUARD);

        // Regenerate session to prevent session fixation
        $request->session()->regenerate();

        return true;
    }
}
