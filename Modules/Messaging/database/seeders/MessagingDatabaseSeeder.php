<?php

namespace Modules\Messaging\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Models\Channel;

class MessagingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedChannels();
    }

    /**
     * Seed the default messaging channels.
     */
    protected function seedChannels(): void
    {
        $channels = [
            [
                'name' => 'WhatsApp',
                'type' => ChannelTypeEnum::WHATSAPP,
                'is_active' => true,
                'is_admin_only' => true,
                'config' => [
                    'api_version' => config('messaging.whatsapp.api_version', 'v18.0'),
                    'phone_number_id' => config('messaging.whatsapp.phone_number_id'),
                    'business_account_id' => config('messaging.whatsapp.business_account_id'),
                    'access_token' => config('messaging.whatsapp.access_token'),
                    'verify_token' => config('messaging.whatsapp.verify_token'),
                    'app_secret' => config('messaging.whatsapp.app_secret'),
                ],
            ],
            [
                'name' => 'Telegram',
                'type' => ChannelTypeEnum::TELEGRAM,
                'is_active' => true,
                'is_admin_only' => true,
                'config' => [
                    'bot_token' => config('messaging.telegram.bot_token'),
                    'bot_username' => config('messaging.telegram.bot_username'),
                    'webhook_secret' => config('messaging.telegram.webhook_secret'),
                ],
            ],
            [
                'name' => 'Web Chat',
                'type' => ChannelTypeEnum::WEBCHAT,
                'is_active' => true,
                'is_admin_only' => false,
                'config' => [
                    'widget_color' => config('messaging.webchat.widget_color', '#6366f1'),
                    'welcome_message' => config('messaging.webchat.welcome_message', 'Hello! How can we help you today?'),
                ],
            ],
        ];

        foreach ($channels as $channelData) {
            Channel::updateOrCreate(
                ['type' => $channelData['type']],
                $channelData
            );
        }
    }
}
