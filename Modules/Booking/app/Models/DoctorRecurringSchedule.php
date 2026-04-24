<?php

/**
 * -----------------------------------------------------------------------------
 * DoctorRecurringSchedule Model
 * -----------------------------------------------------------------------------
 *
 * Represents a weekly recurring availability template for a doctor.
 * Example:
 *   "Every Monday from 09:00 to 17:00, 30 minute slots, $75 each,
 *    effective from 2026-05-01 (indefinite)."
 *
 * These templates are the "source of truth" used by the scheduled job in
 * `GenerateRecurringAvailabilityAction` to produce concrete DoctorAvailability
 * rows for upcoming dates. Exceptions (vacation, holiday, one-off changes)
 * are captured in DoctorScheduleException and override the template for a
 * specific date.
 *
 * Key features:
 *   - Soft deletes: removing a recurring schedule keeps the audit trail and
 *     lets the system know which generated availabilities came from it.
 *   - `day_of_week` follows Carbon's convention (0 = Sunday … 6 = Saturday).
 *   - `effective_from` / `effective_until` define when the pattern is valid;
 *     `effective_until` may be null meaning "forever until deactivated".
 * -----------------------------------------------------------------------------
 */

namespace Modules\Booking\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Doctor\Models\Doctor;

class DoctorRecurringSchedule extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass-assignable columns for doctor_recurring_schedules.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'doctor_id',        // FK → doctors.id (schedule owner)
        'day_of_week',      // 0 (Sun) … 6 (Sat)
        'start_time',       // Daily start (HH:MM)
        'end_time',         // Daily end   (HH:MM)
        'slot_duration',    // Slot length in minutes
        'consultation_fee', // Fee per slot
        'effective_from',   // Date the pattern starts being applied
        'effective_until',  // Date the pattern stops (nullable = forever)
        'is_active',        // Soft toggle without deleting
    ];

    /**
     * Attribute casts.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'day_of_week'      => 'integer',
        'start_time'       => 'datetime:H:i',
        'end_time'         => 'datetime:H:i',
        'slot_duration'    => 'integer',
        'consultation_fee' => 'decimal:2',
        'effective_from'   => 'date',
        'effective_until'  => 'date',
        'is_active'        => 'boolean',
    ];

    /**
     * Mapping between Carbon day-of-week integers and human names.
     * These names are looked up through the booking translation files so the
     * UI can render them in the user's language.
     */
    public const DAYS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    /* -------------------------------------------------------------------------
     |  Relationships
     | -------------------------------------------------------------------------
     */

    /** The doctor this template belongs to. */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** Exceptions (holidays, leaves) that override this template on a given date. */
    public function exceptions(): HasMany
    {
        return $this->hasMany(DoctorScheduleException::class, 'recurring_schedule_id');
    }

    /** DoctorAvailability rows that were auto-generated from this template. */
    public function generatedAvailabilities(): HasMany
    {
        return $this->hasMany(DoctorAvailability::class, 'recurring_schedule_id');
    }

    /* -------------------------------------------------------------------------
     |  Accessors
     | -------------------------------------------------------------------------
     */

    /**
     * Return the localized name of the day (e.g. "Monday" / "الإثنين").
     */
    public function getDayNameAttribute(): string
    {
        return __('booking::days.'.strtolower(self::DAYS[$this->day_of_week]));
    }

    /**
     * Return the window as "HH:MM - HH:MM" for easy UI rendering.
     */
    public function getFormattedTimeRangeAttribute(): string
    {
        return $this->start_time->format('H:i').' - '.$this->end_time->format('H:i');
    }

    /* -------------------------------------------------------------------------
     |  Query Scopes
     | -------------------------------------------------------------------------
     */

    /** Restrict to active recurring schedules. */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Restrict to a specific doctor. */
    public function scopeForDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Restrict to schedules that are effective on a given date — i.e.
     * effective_from <= date AND (effective_until IS NULL OR >= date).
     */
    public function scopeEffectiveOn($query, $date)
    {
        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $date);
            });
    }

    /** Restrict to a specific weekday (0=Sun … 6=Sat). */
    public function scopeForDayOfWeek($query, int $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    /* -------------------------------------------------------------------------
     |  Helpers
     | -------------------------------------------------------------------------
     */

    /**
     * Check whether the recurring schedule is active on the given calendar date.
     * Used by the availability-generation job to decide whether to spawn
     * DoctorAvailability rows for a particular day.
     */
    public function isEffectiveOn($date): bool
    {
        $date = is_string($date) ? Carbon::parse($date) : $date;

        // Not yet within the effective window
        if ($this->effective_from > $date) {
            return false;
        }

        // Past the effective window (only if an end date is specified)
        if ($this->effective_until && $this->effective_until < $date) {
            return false;
        }

        return true;
    }

    /**
     * Build the list of time slots that this recurring schedule would produce
     * for the given date. Does NOT persist anything; pure calculation.
     *
     * @return array<int, array{start_time:string, end_time:string, duration:int, consultation_fee:float}>
     */
    public function generateSlotsForDate($date): array
    {
        $slots = [];
        $date  = is_string($date) ? Carbon::parse($date) : $date;

        // Start by anchoring the template's start/end times to the given date.
        $currentTime = $date->copy()->setTimeFromTimeString($this->start_time->format('H:i:s'));
        $endTime     = $date->copy()->setTimeFromTimeString($this->end_time->format('H:i:s'));

        // Walk forward in slot_duration steps until adding another slot would
        // spill past the window's end time.
        while ($currentTime->copy()->addMinutes($this->slot_duration)->lte($endTime)) {
            $slots[] = [
                'start_time'       => $currentTime->format('H:i'),
                'end_time'         => $currentTime->copy()->addMinutes($this->slot_duration)->format('H:i'),
                'duration'         => $this->slot_duration,
                'consultation_fee' => $this->consultation_fee,
            ];
            $currentTime->addMinutes($this->slot_duration);
        }

        return $slots;
    }
}
