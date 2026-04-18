<?php

namespace Modules\Messaging\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Messaging';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapWebhookRoutes();
        $this->mapBroadcastChannels();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')->group(module_path($this->name, '/routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware('api')->prefix('api')->name('api.')->group(module_path($this->name, '/routes/api.php'));
    }

    /**
     * Define the webhook routes for the application.
     *
     * These routes do not have CSRF protection as they receive external requests.
     */
    protected function mapWebhookRoutes(): void
    {
        Route::middleware('api')
            ->group(module_path($this->name, '/routes/webhooks.php'));
    }

    /**
     * Define the broadcast channel authorization.
     */
    protected function mapBroadcastChannels(): void
    {
        require module_path($this->name, '/routes/channels.php');
    }
}
