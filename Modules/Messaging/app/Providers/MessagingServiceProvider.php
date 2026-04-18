<?php

namespace Modules\Messaging\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Modules\Messaging\Console\Commands\MigrateYeastarDataCommand;
use Modules\Messaging\Console\Commands\SyncWhatsAppTemplatesCommand;
use Modules\Messaging\Contracts\Repositories\ConversationRepositoryInterface;
use Modules\Messaging\Contracts\Repositories\MessageRepositoryInterface;
use Modules\Messaging\Jobs\RetryFailedMessagesJob;
use Modules\Messaging\Livewire\Admin\AnalyticsDashboard;
use Modules\Messaging\Livewire\AgentDashboard;
use Modules\Messaging\Livewire\ConversationContext;
use Modules\Messaging\Livewire\ConversationFilters;
use Modules\Messaging\Livewire\ConversationList;
use Modules\Messaging\Livewire\ConversationSearch;
use Modules\Messaging\Livewire\ConversationThread;
use Modules\Messaging\Livewire\ConversationThreadThemed;
use Modules\Messaging\Livewire\GlobalMessagingIcon;
use Modules\Messaging\Livewire\GlobalMessagingIconThemed;
use Modules\Messaging\Livewire\GlobalMessagingPanel;
use Modules\Messaging\Livewire\GlobalMessagingPanelThemed;
use Modules\Messaging\Livewire\InternalNotes;
use Modules\Messaging\Livewire\Lead\LeadChannelTabs;
use Modules\Messaging\Livewire\Lead\LeadConversationView;
use Modules\Messaging\Livewire\Lead\LeadMessaging;
use Modules\Messaging\Livewire\MessageComposer;
use Modules\Messaging\Livewire\MessageComposerThemed;
use Modules\Messaging\Models\Channel;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;
use Modules\Messaging\Policies\ChannelPolicy;
use Modules\Messaging\Policies\ConversationPolicy;
use Modules\Messaging\Policies\MessagePolicy;
use Modules\Messaging\Repositories\ConversationRepository;
use Modules\Messaging\Repositories\MessageRepository;
use Modules\Messaging\Services\AutoAssignmentService;
use Modules\Messaging\Services\ChatbotService;
use Modules\Messaging\Services\ContactLinkingService;
use Modules\Messaging\Services\ConversationService;
use Modules\Messaging\Services\ConversationTransferService;
use Modules\Messaging\Services\InteractiveMessageService;
use Modules\Messaging\Services\MessageSearchService;
use Modules\Messaging\Services\MessageService;
use Modules\Messaging\Services\MessagingAnalyticsService;
use Modules\Messaging\Services\MessagingService;
use Modules\Messaging\Services\RateLimitService;
use Modules\Messaging\Services\TypingIndicatorService;
use Modules\Messaging\Services\WebhookDeduplicationService;
use Modules\Messaging\Services\WhatsAppMediaService;
use Modules\Messaging\Services\WhatsAppReadReceiptService;
use Modules\Messaging\Services\WhatsAppTemplateSyncService;
use Modules\Messaging\Services\WhatsAppWindowService;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;

class MessagingServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Messaging';

    protected string $nameLower = 'messaging';

    /**
     * Boot the module services.
     */
    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerTranslations();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));

        $this->registerPolicies();
        $this->registerLivewireComponents();
        $this->registerCommandSchedules();
        $this->registerCommands();
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->name, 'config/config.php') => config_path($this->nameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->name, 'config/config.php'),
            $this->nameLower
        );
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->nameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
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
        foreach ($this->app['config']->get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->nameLower)) {
                $paths[] = $path . '/modules/' . $this->nameLower;
            }
        }

        return $paths;
    }

    /**
     * Register policies.
     */
    protected function registerPolicies(): void
    {
        Gate::policy(Channel::class, ChannelPolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);
        Gate::policy(Message::class, MessagePolicy::class);
    }

    /**
     * Register Livewire components.
     */
    protected function registerLivewireComponents(): void
    {
        // Main components
        Livewire::component('messaging-agent-dashboard', AgentDashboard::class);
        Livewire::component('messaging-global-panel', GlobalMessagingPanel::class);
        Livewire::component('messaging-global-icon', GlobalMessagingIcon::class);
        Livewire::component('messaging-global-icon-themed', GlobalMessagingIconThemed::class);
        Livewire::component('messaging-global-panel-themed', GlobalMessagingPanelThemed::class);
        Livewire::component('messaging-conversation-list', ConversationList::class);
        Livewire::component('messaging-conversation-thread', ConversationThread::class);
        Livewire::component('messaging-conversation-thread-themed', ConversationThreadThemed::class);
        Livewire::component('messaging-conversation-context', ConversationContext::class);
        Livewire::component('messaging-conversation-search', ConversationSearch::class);
        Livewire::component('messaging-conversation-filters', ConversationFilters::class);
        Livewire::component('messaging-internal-notes', InternalNotes::class);
        Livewire::component('messaging-message-composer', MessageComposer::class);
        Livewire::component('messaging-message-composer-themed', MessageComposerThemed::class);

        // Lead integration components
        Livewire::component('messaging-lead-messaging', LeadMessaging::class);
        Livewire::component('messaging-lead-channel-tabs', LeadChannelTabs::class);
        Livewire::component('messaging-lead-conversation-view', LeadConversationView::class);

        // Admin components
        Livewire::component('messaging-analytics-dashboard', AnalyticsDashboard::class);
    }

    /**
     * Register command schedules.
     */
    protected function registerCommandSchedules(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Retry failed messages every 30 minutes
            $schedule->job(new RetryFailedMessagesJob)
                ->everyThirtyMinutes()
                ->withoutOverlapping();

            // Sync WhatsApp templates daily
            $schedule->command('messaging:sync-whatsapp-templates')
                ->daily()
                ->withoutOverlapping();
        });
    }

    /**
     * Register console commands.
     */
    protected function registerCommands(): void
    {
        $this->commands([
            MigrateYeastarDataCommand::class,
            SyncWhatsAppTemplatesCommand::class,
        ]);
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);

        $this->registerRepositories();
        $this->registerServices();
    }

    /**
     * Register repositories.
     */
    protected function registerRepositories(): void
    {
        $this->app->singleton(ConversationRepositoryInterface::class, ConversationRepository::class);
        $this->app->singleton(MessageRepositoryInterface::class, MessageRepository::class);
    }

    /**
     * Register services.
     */
    protected function registerServices(): void
    {
        $this->app->singleton(ConversationService::class, function ($app) {
            return new ConversationService(
                $app->make(ConversationRepositoryInterface::class)
            );
        });

        $this->app->singleton(RateLimitService::class);

        $this->app->singleton(MessageService::class, function ($app) {
            return new MessageService(
                $app->make(MessageRepositoryInterface::class),
                $app->make(ConversationService::class),
                $app->make(RateLimitService::class)
            );
        });

        $this->app->singleton(MessagingService::class, function ($app) {
            return new MessagingService(
                $app->make(ConversationService::class),
                $app->make(MessageService::class)
            );
        });

        // WhatsApp specific services
        $this->app->singleton(WhatsAppWindowService::class);
        $this->app->singleton(WhatsAppMediaService::class);
        $this->app->singleton(WhatsAppReadReceiptService::class);
        $this->app->singleton(WhatsAppTemplateSyncService::class);

        // General messaging services
        $this->app->singleton(WebhookDeduplicationService::class);
        $this->app->singleton(ContactLinkingService::class);
        $this->app->singleton(AutoAssignmentService::class);
        $this->app->singleton(TypingIndicatorService::class);
        $this->app->singleton(MessageSearchService::class);
        $this->app->singleton(MessagingAnalyticsService::class);
        $this->app->singleton(InteractiveMessageService::class);
        $this->app->singleton(ConversationTransferService::class);
        $this->app->singleton(ChatbotService::class);
    }
}
