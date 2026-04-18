<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Doctor\Models\Doctor;

class DoctorScheduleException extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'recurring_schedule_id',
        'exception_date',
        'reason',
        'type',
        'alternate_start_time',
        'alternate_end_time',
    ];

    protected $casts = [
        'exception_date' => 'date',
        'alternate_start_time' => 'datetime:H:i',
        'alternate_end_time' => 'datetime:H:i',
    ];

    public const TYPE_SKIP = 'skip';
    public const TYPE_MODIFIED = 'modified';

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function recurringSchedule(): BelongsTo
    {
        return $this->belongsTo(DoctorRecurringSchedule::class, 'recurring_schedule_id');
    }

    public function scopeForDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('exception_date', $date);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('exception_date', [$startDate, $endDate]);
    }

    public function scopeSkipType($query)
    {
        return $query->where('type', self::TYPE_SKIP);
    }

    public function scopeModifiedType($query)
    {
        return $query->where('type', self::TYPE_MODIFIED);
    }

    public function isSkip(): bool
    {
        return $this->type === self::TYPE_SKIP;
    }

    public function isModified(): bool
    {
        return $this->type === self::TYPE_MODIFIED;
    }

    public function getFormattedAlternateTimeRangeAttribute(): ?string
    {
        if (!$this->isModified() || !$this->alternate_start_time || !$this->alternate_end_time) {
            return null;
        }

        return $this->alternate_start_time->format('H:i') . ' - ' . $this->alternate_end_time->format('H:i');
    }
}
