<?php

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

    protected $fillable = [
        'doctor_id',
        'date',
        'start_time',
        'end_time',
        'slot_duration',
        'consultation_fee',
        'is_active',
        'recurring_schedule_id',
        'is_recurring_generated',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'slot_duration' => 'integer',
        'consultation_fee' => 'decimal:2',
        'is_active' => 'boolean',
        'is_recurring_generated' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'doctor_availability_id');
    }

    public function recurringSchedule(): BelongsTo
    {
        return $this->belongsTo(DoctorRecurringSchedule::class, 'recurring_schedule_id');
    }

    public function generateSlots(): array
    {
        $slots = [];
        $start = Carbon::parse($this->date->format('Y-m-d').' '.$this->start_time->format('H:i'));
        $end = Carbon::parse($this->date->format('Y-m-d').' '.$this->end_time->format('H:i'));

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

    public function getAvailableSlots(): array
    {
        $allSlots = $this->generateSlots();
        $bookedSlots = $this->bookings()
            ->whereIn('status', [
                BookingStatusEnum::PENDING->value,
                BookingStatusEnum::CONFIRMED->value,
            ])
            ->pluck('start_time')
            ->map(fn ($t) => Carbon::parse($t)->format('H:i'))
            ->toArray();

        return array_filter($allSlots, fn ($slot) => ! in_array($slot['start'], $bookedSlots));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    public function scopeForDoctor(Builder $query, int $doctorId): Builder
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeRecurringGenerated(Builder $query): Builder
    {
        return $query->where('is_recurring_generated', true);
    }

    public function scopeManuallyCreated(Builder $query): Builder
    {
        return $query->where('is_recurring_generated', false);
    }

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
