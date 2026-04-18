<?php

namespace Modules\Payment\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\AdminManagement\Models\Admin;
use Modules\Booking\Models\Booking;
use Modules\Doctor\Models\Patient;
use Modules\Payment\Enums\PaymentMethodEnum;
use Modules\Payment\Enums\PaymentStatusEnum;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Payment extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'booking_id',
        'patient_id',
        'payment_method',
        'amount',
        'currency',
        'status',
        'transaction_id',
        'paypal_order_id',
        'payment_details',
        'admin_notes',
        'paid_at',
        'verified_at',
        'verified_by_id',
        'verified_by_type',
    ];

    protected $casts = [
        'payment_method' => PaymentMethodEnum::class,
        'amount' => 'decimal:2',
        'status' => PaymentStatusEnum::class,
        'payment_details' => 'array',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function verifiedBy(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatusEnum::PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === PaymentStatusEnum::COMPLETED;
    }

    public function isAwaitingVerification(): bool
    {
        return $this->status === PaymentStatusEnum::AWAITING_VERIFICATION;
    }

    public function isOffline(): bool
    {
        return $this->payment_method === PaymentMethodEnum::OFFLINE;
    }

    public function isPayPal(): bool
    {
        return $this->payment_method === PaymentMethodEnum::PAYPAL;
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PaymentStatusEnum::PENDING);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', PaymentStatusEnum::COMPLETED);
    }

    public function scopeAwaitingVerification(Builder $query): Builder
    {
        return $query->where('status', PaymentStatusEnum::AWAITING_VERIFICATION);
    }

    public function scopeForPatient(Builder $query, int $patientId): Builder
    {
        return $query->where('patient_id', $patientId);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('payment_proofs')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'])
            ->useDisk('secure'); // Store payment proofs in secure (private) storage
    }

    /**
     * Get secure URL for media download.
     */
    public function getSecureMediaUrl($mediaItem): string
    {
        return route('secure-file.download', ['mediaId' => $mediaItem->id]);
    }
}
