<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Models\Channel;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set WhatsApp and Telegram as admin-only
        Channel::whereIn('type', [
            ChannelTypeEnum::WHATSAPP->value,
            ChannelTypeEnum::TELEGRAM->value,
        ])->update(['is_admin_only' => true]);

        // Ensure WebChat is public (not admin-only)
        Channel::where('type', ChannelTypeEnum::WEBCHAT->value)
            ->update(['is_admin_only' => false]);

        // Create WebChat channel if it doesn't exist
        Channel::firstOrCreate(
            ['type' => ChannelTypeEnum::WEBCHAT->value],
            [
                'name' => 'WebChat',
                'is_active' => true,
                'is_admin_only' => false,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally revert to previous state
        // Not reversing as this is a data setup migration
    }
};
