<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Modules\AdminManagement\app\Models\Admin;
use Modules\Auth\Models\SocialAccount;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\Patient;

class SocialController extends Controller
{
    /**
     * Redirect to social provider.
     */
    public function redirect(string $provider, Request $request)
    {
        // Validate provider
        if (! in_array($provider, ['google', 'facebook', 'x'])) {
            abort(404, 'Unsupported social provider');
        }

        // Get user type from request (default to patient)
        $userType = $request->get('user_type', 'patient');

        // Store user type in session for callback
        session(['social_login_user_type' => $userType]);

        $driver = Socialite::driver($provider);

        // Set appropriate scopes based on provider
        if ($provider === 'google') {
            $driver->scopes(['openid', 'profile', 'email']);
        } elseif ($provider === 'facebook') {
            $driver->scopes(['public_profile', 'email']);
        } elseif ($provider === 'x') {
            $driver->scopes(['tweet.read', 'users.read', 'offline.access']);
        }

        return $driver->redirect();
    }

    /**
     * Handle social provider callback.
     */
    public function callback(string $provider)
    {
        try {
            $oauthUser = Socialite::driver($provider)->user();
            $userType = session('social_login_user_type', 'patient');

            // Clear the session
            session()->forget('social_login_user_type');

            // Try to find existing social account
            $socialAccount = SocialAccount::query()
                ->where('provider', $provider)
                ->where('provider_user_id', $oauthUser->getId())
                ->first();

            if ($socialAccount) {
                $user = $socialAccount->user;
            } else {
                // Try to find user by email
                $user = $this->findUserByEmail($oauthUser->getEmail(), $userType);

                if (! $user) {
                    // Create new user
                    $user = $this->createUser($oauthUser, $userType);
                }

                // Create social account
                $user->socialAccounts()->create([
                    'provider' => $provider,
                    'provider_user_id' => (string) $oauthUser->getId(),
                    'token' => $oauthUser->token ?? null,
                    'refresh_token' => $oauthUser->refreshToken ?? null,
                    'expires_in' => $oauthUser->expiresIn ?? null,
                ]);
            }

            // Login the user with appropriate guard
            $this->loginUser($user, $userType);

            // Redirect based on user type
            return $this->getRedirectUrl($userType);

        } catch (\Exception $e) {
            return redirect()->route('admin.login')
                ->with('error', 'Social login failed: '.$e->getMessage());
        }
    }

    /**
     * Find user by email and type.
     */
    private function findUserByEmail(?string $email, string $userType): ?object
    {
        if (! $email) {
            return null;
        }

        return match ($userType) {
            'patient' => Patient::where('email', $email)->first(),
            'doctor' => Doctor::where('email', $email)->first(),
            'admin' => Admin::where('email', $email)->first(),
            'user' => User::where('email', $email)->first(),
            default => null,
        };
    }

    /**
     * Create new user based on type.
     */
    private function createUser($oauthUser, string $userType): object
    {
        $userData = [
            'name' => $oauthUser->getName() ?: $oauthUser->getNickname() ?: 'User',
            'email' => $oauthUser->getEmail(),
            'password' => Hash::make(Str::random(32)),
            'email_verified_at' => now(),
        ];

        return match ($userType) {
            'patient' => Patient::create(array_merge($userData, [
                'is_active' => ActiveEnum::ACTIVE,
            ])),
            'doctor' => Doctor::create(array_merge($userData, [
                'is_active' => ActiveEnum::ACTIVE,
            ])),
            'admin' => Admin::create(array_merge($userData, [
                'is_active' => 1,
            ])),
            'user' => User::create(array_merge($userData, [
                'is_active' => ActiveEnum::ACTIVE,
            ])),
            default => throw new \InvalidArgumentException("Unsupported user type: {$userType}"),
        };
    }

    /**
     * Login user with appropriate guard.
     */
    private function loginUser(object $user, string $userType): void
    {
        match ($userType) {
            'patient' => Auth::guard('web')->login($user, true),
            'doctor' => Auth::guard('doctor')->login($user, true),
            'admin' => Auth::guard('admin')->login($user, true),
            'user' => Auth::login($user, true),
            default => throw new \InvalidArgumentException("Unsupported user type: {$userType}"),
        };
    }

    /**
     * Get redirect URL based on user type.
     */
    private function getRedirectUrl(string $userType): \Illuminate\Http\RedirectResponse
    {
        return match ($userType) {
            'patient' => redirect()->route('landing.home'),
            'doctor' => redirect()->route('doctor.dashboard.index'),
            'admin' => redirect()->route('admin.dashboard.index'),
            'user' => redirect()->route('customer.home'),
            default => redirect()->route('landing.home'),
        };
    }
}
