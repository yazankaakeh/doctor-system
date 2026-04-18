<?php

namespace Modules\Core\app\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Core\App\View\Composers\ThemeSettingsComposer;
use Modules\Core\Contracts\VideoServiceInterface;
use Modules\Core\Livewire\VideoRoom;
use Modules\Core\Services\Video\VideoServiceManager;

class CoreServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Core';

    protected string $moduleNameLower = 'core';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerViewComposers();
        $this->registerLivewireComponents();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
        $this->publishes([
            module_path('Core', 'resources/assets/flags/svg') => public_path('assets/flags'),
            module_path('Core', 'resources/assets/js/intlTelInput') => public_path('intlTelInput'),
            module_path('Core', 'resources/assets/js/livewire-select2') => public_path('livewire-select2'),
        ], 'core-assets');
    }

    /**
     * Register Livewire components.
     */
    protected function registerLivewireComponents(): void
    {
        Livewire::component('core::video-room', VideoRoom::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes(
            [module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower.'.php')],
            'config',
        );
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower);
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/services.php'), $this->moduleNameLower);
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/video.php'), $this->moduleNameLower.'.video');
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace(
            '/',
            '\\',
            config('modules.namespace').'\\'.$this->moduleName.'\\'.config(
                'modules.paths.generator.component-class.path',
            ),
        );
        Blade::componentNamespace($componentNamespace, $this->moduleNameLower);
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }

    /**
     * Register view composers.
     */
    protected function registerViewComposers(): void
    {
        view()->composer('*', ThemeSettingsComposer::class);
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->registerVideoService();
    }

    /**
     * Register video service bindings.
     */
    protected function registerVideoService(): void
    {
        // Register the Video Service Manager as singleton
        $this->app->singleton('video.service', function ($app) {
            return new VideoServiceManager;
        });

        // Bind the interface to the default driver
        $this->app->bind(VideoServiceInterface::class, function ($app) {
            return $app->make('video.service')->driver();
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
}
