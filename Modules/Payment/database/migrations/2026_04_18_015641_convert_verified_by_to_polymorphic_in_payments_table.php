<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['verified_by']);

            // Rename verified_by to verified_by_id
            $table->renameColumn('verified_by', 'verified_by_id');
        });

        // Add verified_by_type column in a separate statement (required for column additions)
        Schema::table('payments', function (Blueprint $table) {
            $table->string('verified_by_type')->nullable()->after('verified_by_id');
        });

        // Update existing records to set verified_by_type to Admin class
        DB::table('payments')
            ->whereNotNull('verified_by_id')
            ->update(['verified_by_type' => 'Modules\\AdminManagement\\Models\\Admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop the polymorphic type column
            $table->dropColumn('verified_by_type');

            // Rename back to verified_by
            $table->renameColumn('verified_by_id', 'verified_by');
        });

        // Re-add the foreign key constraint
        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('verified_by')->references('id')->on('admins')->nullOnDelete();
        });
    }
};
