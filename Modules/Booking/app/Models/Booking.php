<?php

/**
 * -----------------------------------------------------------------------------
 * Booking Model
 * -----------------------------------------------------------------------------
 *
 * Represents a single appointment between a patient and a doctor.
 *
 * A booking holds:
 *   - Scheduling information (date, start/end time, duration)
 *   - Financial information (consultation fee, linked Payment)
 *   - Life-cycle state (pending, confirmed, cancelled, completed) via the
 *     BookingStatusEnum cast.
 *   - Video meeting data (meeting_link / meeting_room_name) that allows the
 *     doctor and patient to join the tele-consultation room.
 *   - Reminder bookkeeping columns that track whether the 24h / 1h email
 *     reminders have been dispatched for both the patient and the doctor.
 *
 * Relationships:
 *   - belongsTo: Patient, Doctor, DoctorAvailability (the slot that was booked)
 *   - hasOne:    Payment (one payment per booking)
 *   - morphOne:  Conversation (ties the booking to a chat thread between
 *                patient & doctor via the Messaging module)
 *
 * The model also provides convenience status helpers (isPending / isConfirmed
 * / isCancelled / isCompleted / canBeCancelled) and query scopes used across
 * the Booking module.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Core\Traits\HasVideoConsultation;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\Patient;
use Modules\Messaging\Models\Conversation;
use Modules\Payment\Models\Payment;

class Booking extends Model
{
    // Enable the Eloquent factory helpers for this model (used in seeders/tests).
    use HasFactory;

    // Adds helpers for generating and validating Jitsi video consultation URLs.
    use HasVideoConsultation;

    /**
     * Columns that are mass-assignable.
     *
     * These match the columns defined in the bookings migration. Any column
     * that must never be written through `create()`/`fill()` (e.g. `id`,
     * `created_at`, `updated_at`) is intentionally excluded.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patient_id',                 // FK → patients.id (who is being seen)
        'doctor_id',                  // FK → doctors.id  (who is seeing them)
        'doctor_availability_id',     // FK → doctor_availabilities.id (the slot)
        'booking_date',               // Calendar date of the appointment
        'start_time',                 // Slot start time (HH:MM)
        'end_time',                   // Slot end time (HH:MM)
        'duration',                   // Duration in minutes (cached for speed)
        'consultation_fee',           // Amount to be paid for this booking
        'status',                     // Enum: pending|confirmed|cancelled|completed
        'cancellation_reason',        // Free-text reason when cancelled
        'cancelled_at',               // Timestamp when the booking was cancelled
        'meeting_link',               // Full URL to the tele-consultation room
        'meeting_room_name',          // Jitsi room name (used to rebuild the link)
        'notes',                      // Patient-supplied notes for the doctor
        'reminder_24h_sent_at',       // Patient 24h reminder sent at (or null)
        'reminder_1h_sent_at',        // Patient 1h  reminder sent at (or null)
        'doctor_reminder_24h_sent_at', // Doctor  24h reminder sent at (or null)
        'doctor_reminder_1h_sent_at', // Doctor  1h  reminder sent at (or null)
    ];

    /**
     * Attribute casting rules.
     *
     * - Dates/times are cast to native Carbon instances so we can manipulate
     *   them easily across the module.
     * - The status column is cast to the BookingStatusEnum so consumers get a
     *   strongly-typed value instead of a raw string.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'booking_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'duration' => 'integer',
        'consultation_fee' => 'decimal:2',
        'status' => BookingStatusEnum::class,
        'cancelled_at' => 'datetime',
        'reminder_24h_sent_at' => 'datetime',
        'reminder_1h_sent_at' => 'datetime',
        'doctor_reminder_24h_sent_at' => 'datetime',
        'doctor_reminder_1h_sent_at' => 'datetime',
    ];

    /* -------------------------------------------------------------------------
     |  Relationships
     | -------------------------------------------------------------------------
     */

    /**
     * The patient who made this booking.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * The doctor being booked.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * The availability row (recurring/one-off slot) this booking consumes.
     */
    public function doctorAvailability(): BelongsTo
    {
        return $this->belongsTo(DoctorAvailability::class, 'doctor_availability_id');
    }

    /**
     * The payment associated with this booking (if any).
     * A booking has at most one payment.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Polymorphic link to the Messaging module's Conversation model.
     *
     * Whenever a booking is created we attach a Conversation so patient and
     * doctor can chat through the messaging UI.
     */
    public function conversation(): MorphOne
    {
        return $this->morphOne(Conversation::class, 'conversable');
    }

    /* -------------------------------------------------------------------------
     |  Status helpers
     | -------------------------------------------------------------------------
     |  These methods wrap BookingStatusEnum comparisons so call-sites stay
     |  readable (e.g. `$booking->isConfirmed()` instead of comparing enums).
     | -------------------------------------------------------------------------
     */

    /** True when the booking is awaiting doctor/admin confirmation. */
    public function isPending(): bool
    {
        return $this->status === BookingStatusEnum::PENDING;
    }

    /** True when the booking has been confirmed and is scheduled. */
    public function isConfirmed(): bool
    {
        return $this->status === BookingStatusEnum::CONFIRMED;
    }

    /** True when the booking was cancelled by either party. */
    public function isCancelled(): bool
    {
        return $this->status === BookingStatusEnum::CANCELLED;
    }

    /** True when the consultation actually took place. */
    public function isCompleted(): bool
    {
        return $this->status === BookingStatusEnum::COMPLETED;
    }

    /**
     * Determines if the booking is still in a cancellable state.
     *
     * Only PENDING and CONFIRMED bookings may be cancelled – once completed
     * or already cancelled a booking cannot transition again.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            BookingStatusEnum::PENDING,
            BookingStatusEnum::CONFIRMED,
        ]);
    }

    /* -------------------------------------------------------------------------
     |  Query Scopes
     | -------------------------------------------------------------------------
     |  Reusable query constraints, intended to keep repositories/controllers
     |  concise and consistent (e.g. Booking::active()->upcoming()->forDoctor(5)).
     | -------------------------------------------------------------------------
     */

    /** Restrict query to pending bookings. */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', BookingStatusEnum::PENDING);
    }

    /** Restrict query to confirmed bookings. */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', BookingStatusEnum::CONFIRMED);
    }

    /** Restrict query to "active" bookings (pending or confirmed). */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            BookingStatusEnum::PENDING,
            BookingStatusEnum::CONFIRMED,
        ]);
    }

    /** Restrict query to bookings on or after today (upcoming appointments). */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('booking_date', '>=', now()->toDateString());
    }

    /** Restrict query to a given patient. */
    public function scopeForPatient(Builder $query, int $patientId): Builder
    {
        return $query->where('patient_id', $patientId);
    }

    /** Restrict query to a given doctor. */
    public function scopeForDoctor(Builder $query, int $doctorId): Builder
    {
        return $query->where('doctor_id', $doctorId);
    }
}
