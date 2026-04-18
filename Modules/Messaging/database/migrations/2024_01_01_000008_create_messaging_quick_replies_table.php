<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('messaging_quick_replies')) {
            Schema::create('messaging_quick_replies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('channel_id')->nullable()->constrained('messaging_channels')->nullOnDelete();
                $table->unsignedBigInteger('user_id')->nullable(); // May reference Admin/Doctor - no FK constraint
                $table->string('title');
                $table->text('content');
                $table->string('shortcut')->nullable(); // e.g., /hello, /thanks
                $table->boolean('is_active')->default(true);
                $table->boolean('is_global')->default(false); // Available to all users
                $table->unsignedInteger('usage_count')->default(0);
                $table->timestamps();

                $table->index(['channel_id', 'is_active']);
                $table->index(['user_id', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('messaging_quick_replies');
    }
};
