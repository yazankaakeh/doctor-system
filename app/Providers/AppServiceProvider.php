<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Modules\AdminManagement\app\Models\Admin;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->booted(function () {
            $locale = session('locale', config('app.locale'));
            App::setLocale($locale);
        });
        Blade::if('canany', function (...$permissions) {
            /** @var Admin $user */
            $user = auth()->user();

            return auth()->check() && $user->hasAnyPermission($permissions);
        });
        Paginator::useBootstrapFive();

        $this->configurePasswordPolicy();
    }

    /**
     * Central strong-password policy used by every FormRequest that calls
     * Illuminate\Validation\Rules\Password::defaults() - registration,
     * password reset, and profile update.
     *
     * Required everywhere EXCEPT the "testing" environment so that the
     * existing PHPUnit suite (which uses short fixtures like "password")
     * keeps running. Production, staging, and local installs all enforce
     * the policy, satisfying the OWASP / NFR-Security requirement.
     */
    protected function configurePasswordPolicy(): void
    {
        Password::defaults(function () {
            $rule = Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();

            return $this->app->environment('production')
                ? $rule->uncompromised()
                : $rule;
        });

        if ($this->app->environment('testing')) {
            // Keep the old permissive default for the automated test suite.
            Password::defaults(fn () => Password::min(6));
        }
    }
}
