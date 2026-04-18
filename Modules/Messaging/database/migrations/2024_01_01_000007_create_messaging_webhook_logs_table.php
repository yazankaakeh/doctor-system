<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('messaging_webhook_logs')) {
            Schema::create('messaging_webhook_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('channel_id')->nullable()->constrained('messaging_channels')->nullOnDelete();
                $table->string('event_type')->nullable();
                $table->json('payload');
                $table->json('headers')->nullable();
                $table->boolean('processed')->default(false);
                $table->text('error')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->index(['channel_id', 'processed']);
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('messaging_webhook_logs');
    }
};
