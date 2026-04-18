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
        Schema::create('notification_push_token', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable'); // if null => customer did not login yet
            $table->text('push_token');
            $table->string('platform')->default('firebase');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_push_token');
    }
};
