<?php

namespace Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\DoctorAvailability;
use Modules\Booking\Models\DoctorRecurringSchedule;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\AdminManagement\Traits\AuditLogTrait;
use Modules\Auth\Models\SocialAccount;
use Modules\Blog\Traits\HasAuthor;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Core\App\Enums\Gender;
use Modules\Core\app\Models\Address;
use Modules\Doctor\Database\Factories\DoctorFactory;
use Modules\Notification\Models\Notification;
use Modules\Notification\Models\NotificationPushToken;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property mixed $name
 * @property mixed $phone
 * @property mixed $email
 * @property mixed|string $password
 * @property int|mixed $is_active
 * @property mixed $gender
 * @property mixed $age
 * @property mixed $medical_specialty_id
 */
class Doctor extends Authenticatable implements HasMedia
{
    use AuditLogTrait;
    use HasAuthor;
    use HasFactory;
    use HasRoles;
    use InteractsWithMedia;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'gender',
        'id',
        'is_active',
        'age',
        'medical_specialty_id',
        'bio',
    ];

    protected $casts = [
        'gender' => Gender::class,
        'is_active' => ActiveEnum::class,
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function newFactory(): DoctorFactory
    {
        return DoctorFactory::new();
    }

    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function medicalSpecialty(): BelongsTo
    {
        return $this->belongsTo(MedicalSpecialty::class, 'medical_specialty_id', 'id');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function pushTokens()
    {
        return $this->morphMany(NotificationPushToken::class, 'tokenable');
    }

    /**
     * Get the social accounts for the doctor.
     */
    public function socialAccounts()
    {
        return $this->morphMany(SocialAccount::class, 'user');
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(DoctorAvailability::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function recurringSchedules(): HasMany
    {
        return $this->hasMany(DoctorRecurringSchedule::class);
    }

    public function activeRecurringSchedules(): HasMany
    {
        return $this->recurringSchedules()->where('is_active', true);
    }

    public function medicalExaminations(): HasMany
    {
        return $this->hasMany(MedicalExamination::class);
    }

    /**
     * Get distinct clinics where doctor has performed examinations.
     */
    public function getClinicsAttribute()
    {
        return Clinic::query()
            ->whereIn('id', $this->medicalExaminations()->pluck('clinic_id')->unique())
            ->where('is_active', 1)
            ->get();
    }

    /**
     * Get count of distinct clinics where doctor works.
     */
    public function getClinicsCountAttribute(): int
    {
        return $this->medicalExaminations()
            ->whereNotNull('clinic_id')
            ->distinct('clinic_id')
            ->count('clinic_id');
    }
}
