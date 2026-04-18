<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('messaging_messages')) {
            Schema::create('messaging_messages', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('conversation_id')->constrained('messaging_conversations')->cascadeOnDelete();

                // Sender information
                $table->string('sender_type'); // user, contact, system
                $table->unsignedBigInteger('sender_id')->nullable(); // No FK constraint - using metadata for actual sender
                $table->string('direction'); // inbound, outbound

                // Message content
                $table->string('message_type')->default('text'); // text, image, document, audio, video, template, location
                $table->text('content')->nullable();
                $table->string('media_url')->nullable();

                // Template info
                $table->foreignId('template_id')->nullable()->constrained('messaging_templates')->nullOnDelete();
                $table->json('template_variables')->nullable();

                // Delivery status
                $table->string('status')->default('pending'); // pending, sent, delivered, read, failed
                $table->string('error_code')->nullable();
                $table->text('error_message')->nullable();

                // Timestamps for delivery tracking
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('read_at')->nullable();

                // External reference
                $table->string('external_message_id')->nullable()->index();
                $table->json('metadata')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index(['conversation_id', 'created_at']);
                $table->index(['status', 'direction']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('messaging_messages');
    }
};
