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
        // First, check if the old table exists
        if (Schema::hasTable('social_accounts')) {
            // Drop the old table if it exists
            Schema::dropIfExists('social_accounts');
        }

        // Create the new polymorphic table
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            // Polymorphic relationship with custom index name to avoid length issues
            $table->string('user_type');
            $table->unsignedBigInteger('user_id');
            $table->index(['user_type', 'user_id'], 'idx_social_acc_user');
            $table->string('provider');          // google, facebook, x
            $table->string('provider_user_id');  // provider's unique id
            $table->text('token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->integer('expires_in')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_user_id'], 'idx_social_acc_provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
