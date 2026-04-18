<?php

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
    use HasFactory;
    use HasVideoConsultation;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'doctor_availability_id',
        'booking_date',
        'start_time',
        'end_time',
        'duration',
        'consultation_fee',
        'status',
        'cancellation_reason',
        'cancelled_at',
        'meeting_link',
        'meeting_room_name',
        'notes',
        'reminder_24h_sent_at',
        'reminder_1h_sent_at',
        'doctor_reminder_24h_sent_at',
        'doctor_reminder_1h_sent_at',
    ];

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

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function doctorAvailability(): BelongsTo
    {
        return $this->belongsTo(DoctorAvailability::class, 'doctor_availability_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function conversation(): MorphOne
    {
        return $this->morphOne(Conversation::class, 'conversable');
    }

    public function isPending(): bool
    {
        return $this->status === BookingStatusEnum::PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === BookingStatusEnum::CONFIRMED;
    }

    public function isCancelled(): bool
    {
        return $this->status === BookingStatusEnum::CANCELLED;
    }

    public function isCompleted(): bool
    {
        return $this->status === BookingStatusEnum::COMPLETED;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            BookingStatusEnum::PENDING,
            BookingStatusEnum::CONFIRMED,
        ]);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', BookingStatusEnum::PENDING);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', BookingStatusEnum::CONFIRMED);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            BookingStatusEnum::PENDING,
            BookingStatusEnum::CONFIRMED,
        ]);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('booking_date', '>=', now()->toDateString());
    }

    public function scopeForPatient(Builder $query, int $patientId): Builder
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeForDoctor(Builder $query, int $doctorId): Builder
    {
        return $query->where('doctor_id', $doctorId);
    }
}
