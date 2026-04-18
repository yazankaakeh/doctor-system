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
        Schema::table('audit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_logs', 'auditable_id')) {
                $table->unsignedBigInteger('auditable_id')->nullable()->after('id');
            }
            if (! Schema::hasColumn('audit_logs', 'auditable_type')) {
                $table->string('auditable_type')->nullable()->after('auditable_id');
            }
        });

        // Add index for the morph columns
        try {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index(['auditable_type', 'auditable_id'], 'audit_logs_auditable_index');
            });
        } catch (Exception $e) {
            // Index might already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            try {
                $table->dropIndex('audit_logs_auditable_index');
            } catch (Exception $e) {
                // Index might not exist
            }
            if (Schema::hasColumn('audit_logs', 'auditable_id')) {
                $table->dropColumn('auditable_id');
            }
            if (Schema::hasColumn('audit_logs', 'auditable_type')) {
                $table->dropColumn('auditable_type');
            }
        });
    }
};
