<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_availabilities', function (Blueprint $table) {
            $table->foreignId('recurring_schedule_id')
                ->nullable()
                ->after('is_active')
                ->constrained('doctor_recurring_schedules')
                ->nullOnDelete();
            $table->boolean('is_recurring_generated')
                ->default(false)
                ->after('recurring_schedule_id');

            $table->index('recurring_schedule_id');
            $table->index('is_recurring_generated');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_availabilities', function (Blueprint $table) {
            $table->dropForeign(['recurring_schedule_id']);
            $table->dropColumn(['recurring_schedule_id', 'is_recurring_generated']);
        });
    }
};
