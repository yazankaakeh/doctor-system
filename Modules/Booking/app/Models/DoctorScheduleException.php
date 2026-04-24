<?php

/**
 * -----------------------------------------------------------------------------
 * DoctorScheduleException Model
 * -----------------------------------------------------------------------------
 *
 * Represents a one-off override to a doctor's recurring schedule for a single
 * calendar date. Common use cases:
 *   - Doctor is on holiday / sick / at a conference → type = "skip"
 *     (no availability is generated for that date).
 *   - Doctor is in clinic but with different hours that day → type = "modified"
 *     (availability is generated from alternate_start_time / alternate_end_time).
 *
 * The exception is anchored to a DoctorRecurringSchedule so the generator
 * knows which template it overrides.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Doctor\Models\Doctor;

class DoctorScheduleException extends Model
{
    use HasFactory;

    /**
     * Mass-assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'doctor_id',             // FK → doctors.id
        'recurring_schedule_id', // FK → doctor_recurring_schedules.id (overridden template)
        'exception_date',        // The specific date this exception applies to
        'reason',                // Free-text reason (holiday, illness, conference…)
        'type',                  // 'skip' or 'modified' (see constants below)
        'alternate_start_time',  // Used only when type = 'modified'
        'alternate_end_time',    // Used only when type = 'modified'
    ];

    /**
     * Attribute casts.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'exception_date'       => 'date',
        'alternate_start_time' => 'datetime:H:i',
        'alternate_end_time'   => 'datetime:H:i',
    ];

    /** Exception type: cancel availability entirely on this date. */
    public const TYPE_SKIP = 'skip';

    /** Exception type: generate availability with the alternate times. */
    public const TYPE_MODIFIED = 'modified';

    /* -------------------------------------------------------------------------
     |  Relationships
     | -------------------------------------------------------------------------
     */

    /** The doctor this exception applies to. */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** The recurring schedule that this exception overrides. */
    public function recurringSchedule(): BelongsTo
    {
        return $this->belongsTo(DoctorRecurringSchedule::class, 'recurring_schedule_id');
    }

    /* -------------------------------------------------------------------------
     |  Query Scopes
     | -------------------------------------------------------------------------
     */

    /** Restrict to a specific doctor. */
    public function scopeForDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /** Restrict to a single exception date. */
    public function scopeForDate($query, $date)
    {
        return $query->where('exception_date', $date);
    }

    /** Restrict to a date range. Used by the generator when spawning slots. */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('exception_date', [$startDate, $endDate]);
    }

    /** Only "skip" exceptions. */
    public function scopeSkipType($query)
    {
        return $query->where('type', self::TYPE_SKIP);
    }

    /** Only "modified" exceptions. */
    public function scopeModifiedType($query)
    {
        return $query->where('type', self::TYPE_MODIFIED);
    }

    /* -------------------------------------------------------------------------
     |  Helpers / Accessors
     | -------------------------------------------------------------------------
     */

    /** True when the exception cancels the entire day. */
    public function isSkip(): bool
    {
        return $this->type === self::TYPE_SKIP;
    }

    /** True when the exception swaps the day's hours for an alternate set. */
    public function isModified(): bool
    {
        return $this->type === self::TYPE_MODIFIED;
    }

    /**
     * Nicely formatted alternate window ("09:00 - 12:00") for UI badges.
     * Returns null when the exception is of type "skip" or when the alternate
     * times have not been provided.
     */
    public function getFormattedAlternateTimeRangeAttribute(): ?string
    {
        if (! $this->isModified() || ! $this->alternate_start_time || ! $this->alternate_end_time) {
            return null;
        }

        return $this->alternate_start_time->format('H:i').' - '.$this->alternate_end_time->format('H:i');
    }
}
