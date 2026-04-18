<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_schedule_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('recurring_schedule_id')
                ->nullable()
                ->constrained('doctor_recurring_schedules')
                ->nullOnDelete();
            $table->date('exception_date');
            $table->string('reason', 500)->nullable();
            $table->enum('type', ['skip', 'modified'])->default('skip');
            $table->time('alternate_start_time')->nullable();
            $table->time('alternate_end_time')->nullable();
            $table->timestamps();

            $table->unique(['doctor_id', 'exception_date', 'recurring_schedule_id'], 'doctor_schedule_exception_unique');
            $table->index(['doctor_id', 'exception_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_schedule_exceptions');
    }
};
