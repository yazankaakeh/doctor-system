<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
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
    }
}
