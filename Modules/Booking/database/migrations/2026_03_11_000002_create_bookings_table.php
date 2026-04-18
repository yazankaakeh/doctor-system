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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('doctor_availability_id')->constrained('doctor_availabilities')->cascadeOnDelete();
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('duration');
            $table->decimal('consultation_fee', 10, 2);
            $table->tinyInteger('status')->default(1);
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('meeting_link')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['doctor_id', 'booking_date', 'start_time'], 'unique_doctor_slot');
            $table->index(['patient_id', 'status']);
            $table->index(['doctor_id', 'booking_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
