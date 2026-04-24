<?php

/**
 * -----------------------------------------------------------------------------
 * DoctorAvailability Model
 * -----------------------------------------------------------------------------
 *
 * Represents a single window of time during which a doctor is available to
 * take appointments. Examples:
 *   - "Dr. Smith is available on 2026-05-10 from 09:00 to 12:00, 30 minute
 *      slots, $75 per consultation."
 *
 * A DoctorAvailability row may be:
 *   - Manually created by the doctor for a specific date (one-off slot).
 *   - Automatically generated from a DoctorRecurringSchedule (weekly pattern),
 *     in which case `is_recurring_generated` is true and `recurring_schedule_id`
 *     points back to its parent template.
 *
 * The model generates 5/10/15/30-minute slot arrays from its start/end window
 * and knows which of those slots are already booked so the UI can present
 * only truly available options to patients.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Booking\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Doctor\Models\Doctor;

class DoctorAvailability extends Model
{
    use HasFactory;

    /**
     * Mass-assignable columns for the doctor_availabilities table.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'doctor_id',               // FK → doctors.id
        'date',                    // Calendar date of the window
        'start_time',              // Window start (e.g. 09:00)
        'end_time',                // Window end   (e.g. 12:00)
        'slot_duration',           // Minutes per slot (5/10/15/30/60)
        'consultation_fee',        // Fee charged per slot
        'is_active',               // Soft-disable without deleting
        'recurring_schedule_id',   // Parent recurring template (nullable)
        'is_recurring_generated',  // Whether this row was auto-generated
    ];

    /**
     * Attribute casts.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'slot_duration' => 'integer',
        'consultation_fee' => 'decimal:2',
        'is_active' => 'boolean',
        'is_recurring_generated' => 'boolean',
    ];

    /* -------------------------------------------------------------------------
     |  Relationships
     | -------------------------------------------------------------------------
     */

    /** The doctor who owns this availability window. */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** Bookings that were made against this availability window. */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'doctor_availability_id');
    }

    /** If this row was generated from a recurring template, link to it. */
    public function recurringSchedule(): BelongsTo
    {
        return $this->belongsTo(DoctorRecurringSchedule::class, 'recurring_schedule_id');
    }

    /* -------------------------------------------------------------------------
     |  Slot calculation
     | -------------------------------------------------------------------------
     */

    /**
     * Break the availability window into discrete slots based on slot_duration.
     *
     * Example: 09:00–10:00 with a 30 minute slot produces:
     *   [ ['start'=>'09:00','end'=>'09:30'], ['start'=>'09:30','end'=>'10:00'] ]
     *
     * @return array<int, array{start:string,end:string}>
     */
    public function generateSlots(): array
    {
        $slots = [];

        // Combine the date with start/end times to build absolute timestamps
        // so we can step through the window.
        $start = Carbon::parse($this->date->format('Y-m-d').' '.$this->start_time->format('H:i'));
        $end = Carbon::parse($this->date->format('Y-m-d').' '.$this->end_time->format('H:i'));

        // Advance by slot_duration until the next slot would spill past $end.
        while ($start->copy()->addMinutes($this->slot_duration)->lte($end)) {
            $slotEnd = $start->copy()->addMinutes($this->slot_duration);

            $slots[] = [
                'start' => $start->format('H:i'),
                'end' => $slotEnd->format('H:i'),
            ];

            $start = $slotEnd;
        }

        return $slots;
    }

    /**
     * Return only the slots that are not already booked.
     *
     * A slot is considered booked if an associated Booking exists in PENDING
     * or CONFIRMED state whose start_time matches the slot start.
     *
     * @return array<int, array{start:string,end:string}>
     */
    public function getAvailableSlots(): array
    {
        $allSlots = $this->generateSlots();

        // Collect start times of active bookings (HH:MM strings) so we can
        // compare them with the generated slot list.
        $bookedSlots = $this->bookings()
            ->whereIn('status', [
                BookingStatusEnum::PENDING->value,
                BookingStatusEnum::CONFIRMED->value,
            ])
            ->pluck('start_time')
            ->map(fn ($t) => Carbon::parse($t)->format('H:i'))
            ->toArray();

        // Filter out any slot whose start time matches a booked slot.
        return array_filter($allSlots, fn ($slot) => ! in_array($slot['start'], $bookedSlots));
    }

    /* -------------------------------------------------------------------------
     |  Query Scopes
     | -------------------------------------------------------------------------
     */

    /** Only active (non soft-disabled) availability rows. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Only availabilities on or after today. */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    /** Restrict to a specific doctor. */
    public function scopeForDoctor(Builder $query, int $doctorId): Builder
    {
        return $query->where('doctor_id', $doctorId);
    }

    /** Restrict to a given [startDate, endDate] calendar range. */
    public function scopeForDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /** Only rows that were auto-generated from a recurring template. */
    public function scopeRecurringGenerated(Builder $query): Builder
    {
        return $query->where('is_recurring_generated', true);
    }

    /** Only rows that were created manually by the doctor. */
    public function scopeManuallyCreated(Builder $query): Builder
    {
        return $query->where('is_recurring_generated', false);
    }

    /**
     * True when there is at least one pending or confirmed booking tied to
     * this availability. Used to decide if it's safe to delete the row.
     */
    public function hasActiveBookings(): bool
    {
        return $this->bookings()
            ->whereIn('status', [
                BookingStatusEnum::PENDING->value,
                BookingStatusEnum::CONFIRMED->value,
            ])
            ->exists();
    }
}
