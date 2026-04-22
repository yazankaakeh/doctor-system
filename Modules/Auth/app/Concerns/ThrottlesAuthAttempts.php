<?php

namespace Modules\Auth\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Shared rate-limiting logic for Doctor/Patient (and any future guard's)
 * login actions. Keyed on email + IP + guard so:
 *
 *   - A patient's failed attempts never lock a doctor with the same email.
 *   - A malicious actor hitting one email from their own IP cannot lock the
 *     real user from logging in at their own IP.
 *
 * Usage inside a LoginAction:
 *
 *     $this->ensureIsNotRateLimited($email, $request, 'doctor');
 *     // ... Auth::attempt() ...
 *     if (! $ok) {
 *         $this->hitRateLimiter($email, $request, 'doctor');
 *         throw ValidationException::withMessages([...]);
 *     }
 *     $this->clearRateLimiter($email, $request, 'doctor');
 */
trait ThrottlesAuthAttempts
{
    /**
     * Max attempts allowed before the limiter kicks in.
     */
    protected int $maxLoginAttempts = 5;

    /**
     * How long (seconds) the lockout lasts after the last failed attempt.
     */
    protected int $loginDecaySeconds = 60;

    /**
     * Build the unique rate-limit bucket for this (email, IP, guard) tuple.
     */
    protected function throttleKey(string $email, Request $request, string $guard): string
    {
        return Str::transliterate(
            Str::lower(trim($email)).'|'.$request->ip().'|'.$guard
        );
    }

    /**
     * Throw a 429 ValidationException if the bucket is already exhausted.
     *
     * @throws ValidationException
     */
    protected function ensureIsNotRateLimited(string $email, Request $request, string $guard): void
    {
        $key = $this->throttleKey($email, $request, $guard);

        if (! RateLimiter::tooManyAttempts($key, $this->maxLoginAttempts)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'email' => trans('auth::auth.throttle', [
                'seconds' => $seconds,
                'minutes' => (int) ceil($seconds / 60),
            ]),
        ])->status(429);
    }

    /**
     * Record a failed attempt against the bucket.
     */
    protected function hitRateLimiter(string $email, Request $request, string $guard): void
    {
        RateLimiter::hit(
            $this->throttleKey($email, $request, $guard),
            $this->loginDecaySeconds
        );
    }

    /**
     * Clear the bucket (call on successful login).
     */
    protected function clearRateLimiter(string $email, Request $request, string $guard): void
    {
        RateLimiter::clear($this->throttleKey($email, $request, $guard));
    }
}
