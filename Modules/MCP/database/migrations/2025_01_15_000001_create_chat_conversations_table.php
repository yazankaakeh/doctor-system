<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            // Polymorphic relationship with custom index name
            $table->string('conversationable_type')->nullable();
            $table->unsignedBigInteger('conversationable_id')->nullable();
            $table->index(['conversationable_type', 'conversationable_id'], 'idx_chat_conv_morph');
            $table->string('session_id')->unique()->index();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->enum('status', ['active', 'closed', 'archived'])->default('active');
            $table->json('metadata')->nullable(); // Store additional context
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Custom index names to avoid MySQL 64 character limit
            $table->index('status', 'idx_chat_conv_status');
            $table->index('created_at', 'idx_chat_conv_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};
