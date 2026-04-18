<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Relax two NOT NULL columns that break mass-creation in tests and
     * legitimate flows:
     *   - `admins.phone`        — phone is optional for most admins
     *   - `permissions.section` — the project added a `section` column on
     *     top of Spatie's `permissions` table, but Spatie's own
     *     `Permission::create()` never passes it, so any code path that
     *     goes through Spatie's helpers crashed with a NOT NULL violation.
     *
     * Both columns stay in the schema — this only removes the NOT NULL
     * constraint and sets a sensible default.
     */
    public function up(): void
    {
        if (Schema::hasTable('admins') && Schema::hasColumn('admins', 'phone')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->string('phone')->nullable()->change();
            });
        }

        if (Schema::hasTable('permissions') && Schema::hasColumn('permissions', 'section')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('section')->nullable()->default(null)->change();
            });
        }
    }

    public function down(): void
    {
        // No-op: reverting to NOT NULL on existing nullable data would fail
        // and there's no functional downside to leaving them relaxed.
    }
};
