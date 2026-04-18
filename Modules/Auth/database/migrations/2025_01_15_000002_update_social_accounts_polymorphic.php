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
        // Check if the table exists and has the old structure
        if (Schema::hasTable('social_accounts')) {
            // Check if it has the old patient_id column
            if (Schema::hasColumn('social_accounts', 'patient_id')) {
                // Drop the old foreign key constraint
                Schema::table('social_accounts', function (Blueprint $table) {
                    $table->dropForeign(['patient_id']);
                });

                // Drop the old column
                Schema::table('social_accounts', function (Blueprint $table) {
                    $table->dropColumn('patient_id');
                });

                // Add the new polymorphic columns
                Schema::table('social_accounts', function (Blueprint $table) {
                    $table->morphs('user'); // Creates user_id and user_type columns
                });

                // Add the new index
                Schema::table('social_accounts', function (Blueprint $table) {
                    $table->index(['user_type', 'user_id']);
                });
            }
        } else {
            // Create the table if it doesn't exist
            Schema::create('social_accounts', function (Blueprint $table) {
                $table->id();
                $table->morphs('user'); // Creates user_id and user_type columns
                $table->string('provider');          // google, facebook, x
                $table->string('provider_user_id');  // provider's unique id
                $table->text('token')->nullable();
                $table->text('refresh_token')->nullable();
                $table->integer('expires_in')->nullable();
                $table->timestamps();

                $table->unique(['provider', 'provider_user_id']);
                $table->index(['user_type', 'user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('social_accounts')) {
            // Remove the polymorphic columns and add back patient_id
            Schema::table('social_accounts', function (Blueprint $table) {
                $table->dropIndex(['user_type', 'user_id']);
                $table->dropColumn(['user_id', 'user_type']);
                $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            });
        }
    }
};
