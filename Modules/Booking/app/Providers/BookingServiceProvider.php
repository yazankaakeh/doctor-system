<?php

namespace Modules\Booking\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Booking\Actions\Booking\ConfirmBookingAction;
use Modules\Booking\Actions\Booking\CreateBookingConversationAction;
use Modules\Booking\Console\CreateMissingConversations;
use Modules\Booking\Console\GenerateRecurringAvailabilitiesCommand;
use Modules\Booking\Console\RegenerateMeetingRoom;
use Modules\Booking\Console\SendAppointmentRemindersCommand;
use Modules\Booking\Livewire\BookingConversation;
use Modules\Booking\Livewire\Doctor\AppointmentCalendar;
use Modules\Booking\Livewire\Doctor\AvailabilityCalendar;
use Modules\Booking\Livewire\Doctor\RecurringScheduleManager;
use Modules\Booking\Livewire\Patient\BookingWizard;
use Modules\Booking\Livewire\PublicBookingWizard;
use Modules\Booking\Repository\Availability\AvailabilityInterface;
use Modules\Booking\Repository\Availability\AvailabilityRepository;
use Modules\Booking\Repository\Booking\BookingInterface;
use Modules\Booking\Repository\Booking\BookingRepository;
use Modules\Booking\Repository\RecurringSchedule\RecurringScheduleInterface;
use Modules\Booking\Repository\RecurringSchedule\RecurringScheduleRepository;
use Modules\Booking\Repository\ScheduleException\ScheduleExceptionInterface;
use Modules\Booking\Repository\ScheduleException\ScheduleExceptionRepository;
use Modules\Messaging\Services\ConversationService;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class BookingServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Booking';

    protected string $nameLower = 'booking';

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
        $this->registerLivewireComponents();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        $this->app->bind(AvailabilityInterface::class, AvailabilityRepository::class);
        $this->app->bind(BookingInterface::class, BookingRepository::class);
        $this->app->bind(RecurringScheduleInterface::class, RecurringScheduleRepository::class);
        $this->app->bind(ScheduleExceptionInterface::class, ScheduleExceptionRepository::class);

        // Conditionally bind CreateBookingConversationAction based on Messaging module availability
        $this->app->when(ConfirmBookingAction::class)
            ->needs(CreateBookingConversationAction::class)
            ->give(function ($app) {
                // Check if Messaging module is enabled
                if (class_exists(ConversationService::class)) {
                    try {
                        return $app->make(CreateBookingConversationAction::class);
                    } catch (\Exception $e) {
                        return null;
                    }
                }

                return null;
            });
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            GenerateRecurringAvailabilitiesCommand::class,
            SendAppointmentRemindersCommand::class,
            CreateMissingConversations::class,
            RegenerateMeetingRoom::class,
        ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Send appointment reminders every 10 minutes
            // This ensures reminders are sent within the appropriate time windows
            $schedule->command('booking:send-reminders')
                ->everyTenMinutes()
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/appointment-reminders.log'));

            // Generate recurring availabilities daily at midnight
            $schedule->command('booking:generate-availabilities --days=14')
                ->dailyAt('00:00')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/availability-generation.log'));
        });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $configPath = module_path($this->name, config('modules.paths.generator.config.path'));

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $config = str_replace($configPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $config_key = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                    $segments = explode('.', $this->nameLower.'.'.$config_key);

                    // Remove duplicated adjacent segments
                    $normalized = [];
                    foreach ($segments as $segment) {
                        if (end($normalized) !== $segment) {
                            $normalized[] = $segment;
                        }
                    }

                    $key = ($config === 'config.php') ? $this->nameLower : implode('.', $normalized);

                    $this->publishes([$file->getPathname() => config_path($config)], 'config');
                    $this->merge_config_from($file->getPathname(), $key);
                }
            }
        }
    }

    /**
     * Merge config from the given path recursively.
     */
    protected function merge_config_from(string $path, string $key): void
    {
        $existing = config($key, []);
        $module_config = require $path;

        config([$key => array_replace_recursive($existing, $module_config)]);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        Blade::componentNamespace(config('modules.namespace').'\\'.$this->name.'\\View\\Components', $this->nameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }

    /**
     * Register Livewire components.
     */
    protected function registerLivewireComponents(): void
    {
        Livewire::component('booking::booking-wizard', BookingWizard::class);
        Livewire::component('booking::public-booking-wizard', PublicBookingWizard::class);
        Livewire::component('booking::recurring-schedule-manager', RecurringScheduleManager::class);
        Livewire::component('booking::availability-calendar', AvailabilityCalendar::class);
        Livewire::component('booking::appointment-calendar', AppointmentCalendar::class);
        Livewire::component('booking::booking-conversation', BookingConversation::class);
    }
}
