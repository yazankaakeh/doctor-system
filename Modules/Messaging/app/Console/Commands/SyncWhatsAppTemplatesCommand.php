<?php

namespace Modules\Messaging\Console\Commands;

use Illuminate\Console\Command;
use Modules\Messaging\Services\WhatsAppTemplateSyncService;

class SyncWhatsAppTemplatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'messaging:sync-whatsapp-templates
                            {--cleanup : Also disable templates that no longer exist in WhatsApp}';

    /**
     * The console command description.
     */
    protected $description = 'Synchronize WhatsApp message templates from Meta Business API';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppTemplateSyncService $service): int
    {
        $this->info('Starting WhatsApp template synchronization...');

        if (! $service->isConfigured()) {
            $this->error('WhatsApp channel is not properly configured.');
            $this->error('Make sure you have set access_token and business_account_id in the channel configuration.');
            return self::FAILURE;
        }

        // Sync templates
        $result = $service->syncAll();

        if (! $result['success']) {
            $this->error($result['message']);
            return self::FAILURE;
        }

        $this->info($result['message']);
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Templates', $result['synced']],
                ['Created', $result['created']],
                ['Updated', $result['updated']],
            ]
        );

        // Cleanup orphaned templates if requested
        if ($this->option('cleanup')) {
            $this->info('Cleaning up orphaned templates...');
            $disabled = $service->cleanupOrphaned();
            $this->info("Disabled {$disabled} templates that no longer exist in WhatsApp.");
        }

        $this->info('Template synchronization completed successfully!');

        return self::SUCCESS;
    }
}
