<?php

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

    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_duration',
        'consultation_fee',
        'effective_from',
        'effective_until',
        'is_active',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'slot_duration' => 'integer',
        'consultation_fee' => 'decimal:2',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    public const DAYS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(DoctorScheduleException::class, 'recurring_schedule_id');
    }

    public function generatedAvailabilities(): HasMany
    {
        return $this->hasMany(DoctorAvailability::class, 'recurring_schedule_id');
    }

    public function getDayNameAttribute(): string
    {
        return __('booking::days.'.strtolower(self::DAYS[$this->day_of_week]));
    }

    public function getFormattedTimeRangeAttribute(): string
    {
        return $this->start_time->format('H:i').' - '.$this->end_time->format('H:i');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeEffectiveOn($query, $date)
    {
        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $date);
            });
    }

    public function scopeForDayOfWeek($query, int $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    public function isEffectiveOn($date): bool
    {
        $date = is_string($date) ? Carbon::parse($date) : $date;

        if ($this->effective_from > $date) {
            return false;
        }

        if ($this->effective_until && $this->effective_until < $date) {
            return false;
        }

        return true;
    }

    public function generateSlotsForDate($date): array
    {
        $slots = [];
        $date = is_string($date) ? Carbon::parse($date) : $date;

        $currentTime = $date->copy()->setTimeFromTimeString($this->start_time->format('H:i:s'));
        $endTime = $date->copy()->setTimeFromTimeString($this->end_time->format('H:i:s'));

        while ($currentTime->copy()->addMinutes($this->slot_duration)->lte($endTime)) {
            $slots[] = [
                'start_time' => $currentTime->format('H:i'),
                'end_time' => $currentTime->copy()->addMinutes($this->slot_duration)->format('H:i'),
                'duration' => $this->slot_duration,
                'consultation_fee' => $this->consultation_fee,
            ];
            $currentTime->addMinutes($this->slot_duration);
        }

        return $slots;
    }
}
