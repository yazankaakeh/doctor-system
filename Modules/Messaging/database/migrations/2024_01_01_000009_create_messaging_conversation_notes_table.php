<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('messaging_conversation_notes')) {
            Schema::create('messaging_conversation_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained('messaging_conversations')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id'); // May reference Admin/Doctor - no FK constraint
                $table->text('content');
                $table->timestamps();

                $table->index(['conversation_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('messaging_conversation_notes');
    }
};
