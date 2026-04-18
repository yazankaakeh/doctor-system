<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('messaging_conversations')) {
            Schema::create('messaging_conversations', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('channel_id')->constrained('messaging_channels')->cascadeOnDelete();

                // Polymorphic relationship to Lead, User, or any model
                $table->string('conversable_type')->nullable();
                $table->unsignedBigInteger('conversable_id')->nullable();

                // Agent assignment (nullable - may reference Admin, Doctor, or other models)
                $table->unsignedBigInteger('assigned_user_id')->nullable();

                // Participant info (external contact)
                $table->string('participant_identifier')->index(); // phone, telegram_id, etc.
                $table->string('participant_name')->nullable();

                // Conversation state
                $table->string('status')->default('open'); // open, pending, resolved, closed
                $table->string('priority')->default('normal'); // low, normal, high, urgent

                // Metadata
                $table->timestamp('last_message_at')->nullable();
                $table->unsignedInteger('unread_count')->default(0);
                $table->string('external_conversation_id')->nullable()->index(); // External system reference
                $table->json('metadata')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index(['conversable_type', 'conversable_id']);
                $table->index(['channel_id', 'participant_identifier']);
                $table->index(['status', 'assigned_user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('messaging_conversations');
    }
};
