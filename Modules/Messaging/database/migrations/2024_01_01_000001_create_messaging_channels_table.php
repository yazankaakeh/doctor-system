<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('messaging_channels')) {
            Schema::create('messaging_channels', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type')->index(); // whatsapp, telegram, sms, webchat
                $table->boolean('is_active')->default(true);
                $table->boolean('is_admin_only')->default(false);
                $table->json('config')->nullable(); // Channel-specific configuration
                $table->json('metadata')->nullable(); // Additional data
                $table->timestamps();

                $table->unique(['type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('messaging_channels');
    }
};
