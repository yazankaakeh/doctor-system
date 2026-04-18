<?php

namespace Modules\Messaging\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\ConversationStatusEnum;
use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Enums\MessageStatusEnum;
use Modules\Messaging\Enums\MessageTypeEnum;
use Modules\Messaging\Enums\SenderTypeEnum;
use Modules\Messaging\Models\Channel;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;

class MigrateYeastarDataCommand extends Command
{
    protected $signature = 'messaging:migrate-yeastar
                            {--dry-run : Run without making changes}
                            {--limit= : Limit number of records to migrate}';

    protected $description = 'Migrate WhatsApp data from Yeastar/CRM module to Messaging module';

    protected ?Channel $whatsappChannel = null;

    protected int $conversationsMigrated = 0;

    protected int $messagesMigrated = 0;

    protected int $errors = 0;

    public function handle(): int
    {
        $this->info('Starting Yeastar data migration...');

        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit');

        if ($dryRun) {
            $this->warn('Running in DRY RUN mode. No changes will be made.');
        }

        // Get WhatsApp channel
        $this->whatsappChannel = Channel::where('type', ChannelTypeEnum::WHATSAPP)->first();

        if (! $this->whatsappChannel) {
            $this->error('WhatsApp channel not found. Please run the seeder first.');

            return Command::FAILURE;
        }

        // Check if source tables exist
        if (! $this->checkSourceTables()) {
            $this->error('Source tables not found. Make sure the CRM module is installed.');

            return Command::FAILURE;
        }

        // Migrate conversations and messages
        $this->migrateConversations($dryRun, $limit);

        // Summary
        $this->newLine();
        $this->info('Migration completed!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Conversations migrated', $this->conversationsMigrated],
                ['Messages migrated', $this->messagesMigrated],
                ['Errors', $this->errors],
            ]
        );

        return Command::SUCCESS;
    }

    protected function checkSourceTables(): bool
    {
        return DB::getSchemaBuilder()->hasTable('lead_whatsapp_messages');
    }

    protected function migrateConversations(bool $dryRun, ?int $limit): void
    {
        $this->info('Fetching leads with WhatsApp conversations...');

        // Get unique lead+phone combinations from messages
        $query = DB::table('lead_whatsapp_messages')
            ->select('lead_id', 'phone_number')
            ->distinct();

        if ($limit) {
            $query->limit($limit);
        }

        $leadPhonePairs = $query->get();

        $this->info("Found {$leadPhonePairs->count()} unique conversations to migrate.");

        $bar = $this->output->createProgressBar($leadPhonePairs->count());
        $bar->start();

        foreach ($leadPhonePairs as $pair) {
            try {
                $this->migrateConversation($pair->lead_id, $pair->phone_number, $dryRun);
                $this->conversationsMigrated++;
            } catch (\Exception $e) {
                $this->errors++;
                $this->newLine();
                $this->error("Error migrating conversation for lead {$pair->lead_id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    protected function migrateConversation(int $leadId, string $phoneNumber, bool $dryRun): void
    {
        // Check if conversation already exists
        $existingConversation = Conversation::where('channel_id', $this->whatsappChannel->id)
            ->where('participant_identifier', $phoneNumber)
            ->where('conversable_type', 'Modules\\CRM\\Models\\Lead')
            ->where('conversable_id', $leadId)
            ->first();

        if ($existingConversation) {
            // Skip already migrated
            return;
        }

        // Get lead info
        /** @var object|null $lead */
        $lead = DB::table('leads')->find($leadId);

        // Get lead user info
        /** @var object|null $leadUser */
        $leadUser = $lead ? DB::table('users')->find($lead->user_id) : null;

        if ($dryRun) {
            // Count messages that would be migrated
            $messageCount = DB::table('lead_whatsapp_messages')
                ->where('lead_id', $leadId)
                ->where('phone_number', $phoneNumber)
                ->count();

            $this->messagesMigrated += $messageCount;

            return;
        }

        // Create conversation
        $conversation = Conversation::create([
            'uuid' => Str::uuid(),
            'channel_id' => $this->whatsappChannel->id,
            'conversable_type' => 'Modules\\CRM\\Models\\Lead',
            'conversable_id' => $leadId,
            'assigned_user_id' => $lead?->assigned_user_id,
            'participant_identifier' => $phoneNumber,
            'participant_name' => $leadUser?->name ?? 'Unknown',
            'status' => ConversationStatusEnum::OPEN,
            'metadata' => [
                'migrated_from' => 'yeastar',
                'migrated_at' => now()->toIso8601String(),
            ],
        ]);

        // Migrate messages
        $this->migrateMessages($conversation, $leadId, $phoneNumber);
    }

    protected function migrateMessages(Conversation $conversation, int $leadId, string $phoneNumber): void
    {
        $messages = DB::table('lead_whatsapp_messages')
            ->where('lead_id', $leadId)
            ->where('phone_number', $phoneNumber)
            ->orderBy('created_at')
            ->get();

        foreach ($messages as $oldMessage) {
            Message::create([
                'uuid' => Str::uuid(),
                'conversation_id' => $conversation->id,
                'sender_type' => $oldMessage->direction === 'out' ? SenderTypeEnum::USER : SenderTypeEnum::CONTACT,
                'sender_id' => $oldMessage->direction === 'out' ? $oldMessage->sender_id : null,
                'direction' => $oldMessage->direction === 'out' ? MessageDirectionEnum::OUTBOUND : MessageDirectionEnum::INBOUND,
                'message_type' => $this->mapMessageType($oldMessage->message_type ?? 'text'),
                'content' => $oldMessage->content ?? $oldMessage->message ?? '',
                'media_url' => $oldMessage->media_url ?? null,
                'status' => $this->mapMessageStatus($oldMessage->status ?? 'sent'),
                'external_message_id' => $oldMessage->yeastar_message_id ?? $oldMessage->external_id ?? null,
                'sent_at' => $oldMessage->sent_at ?? $oldMessage->created_at,
                'delivered_at' => $oldMessage->delivered_at ?? null,
                'read_at' => $oldMessage->read_at ?? null,
                'created_at' => $oldMessage->created_at,
                'updated_at' => $oldMessage->updated_at,
                'metadata' => [
                    'migrated_from' => 'yeastar',
                    'original_id' => $oldMessage->id,
                ],
            ]);

            $this->messagesMigrated++;
        }

        // Update conversation last_message_at
        $lastMessage = $messages->last();
        if ($lastMessage) {
            $conversation->update([
                'last_message_at' => $lastMessage->created_at,
            ]);
        }
    }

    protected function mapMessageType(?string $type): MessageTypeEnum
    {
        return match ($type) {
            'text' => MessageTypeEnum::TEXT,
            'image' => MessageTypeEnum::IMAGE,
            'document', 'file' => MessageTypeEnum::DOCUMENT,
            'audio', 'voice' => MessageTypeEnum::AUDIO,
            'video' => MessageTypeEnum::VIDEO,
            'template' => MessageTypeEnum::TEMPLATE,
            'location' => MessageTypeEnum::LOCATION,
            default => MessageTypeEnum::TEXT,
        };
    }

    protected function mapMessageStatus(?string $status): MessageStatusEnum
    {
        return match ($status) {
            'pending' => MessageStatusEnum::PENDING,
            'sent' => MessageStatusEnum::SENT,
            'delivered' => MessageStatusEnum::DELIVERED,
            'read' => MessageStatusEnum::READ,
            'failed' => MessageStatusEnum::FAILED,
            default => MessageStatusEnum::SENT,
        };
    }
}
