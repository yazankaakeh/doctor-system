<?php

namespace Modules\Doctor\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Auth\Models\SocialAccount;
use Modules\Auth\Notifications\PatientVerifyEmailNotification;
use Modules\Booking\Models\Booking;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Core\App\Enums\Gender;
use Modules\Core\app\Models\Address;
use Modules\Core\app\Models\Country;
use Modules\Doctor\Database\Factories\PatientFactory;
use Modules\Doctor\Enums\BloodType;
use Modules\Doctor\Enums\MaritalStatus;
use Modules\Notification\Models\Notification;
use Modules\Notification\Models\NotificationPushToken;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;

/**
 * @property mixed $clinics
 * @property mixed $name
 * @property mixed $age
 * @property mixed $blood_type
 * @property mixed $gender
 * @property mixed $children
 * @property mixed $is_active
 * @property mixed $marital_status
 * @property mixed $nationality_id
 * @property mixed $work
 * @property mixed $phone
 * @property mixed $drug_allergies
 * @property mixed $disabilities
 * @property mixed $medical_history
 * @property mixed $surgical_history
 * @property mixed $accident_history
 * @property mixed $password
 * @property mixed $email
 * @property mixed $id
 */
class Patient extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasFactory;
    use InteractsWithMedia;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'age',
        'blood_type',
        'gender',
        'children',
        'is_active',
        'marital_status',
        'nationality_id',
        'work',
        'phone',
        'drug_allergies',
        'disabilities',
        'medical_history',
        'surgical_history',
        'accident_history',
        'password',
        'email',
        'id',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'blood_type' => BloodType::class,
        'is_active' => ActiveEnum::class,
        'gender' => Gender::class,
        'marital_status' => MaritalStatus::class,
    ];

    protected static function newFactory(): PatientFactory
    {
        return PatientFactory::new();
    }

    public function clinics(): BelongsToMany
    {
        return $this
            ->belongsToMany(Clinic::class, 'clinic_patients')
            ->using(ClinicPatient::class);
    }

    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('attachments')
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'application/pdf',
                ], true);
            })
            ->useDisk('secure'); // Store patient files in secure (private) storage
    }

    /**
     * Get secure URL for media download.
     */
    public function getSecureMediaUrl($mediaItem): string
    {
        return route('secure-file.download', ['mediaId' => $mediaItem->id]);
    }

    public function nationality(): BelongsTo
    {
        return $this
            ->belongsTo(Country::class, 'nationality_id'); // optional
    }

    public function scopeFilter(Builder $q, array $f): Builder
    {
        // small helpers
        $toArray = fn ($v) => is_array($v) ? $v : (isset($v) && $v !== '' ? [$v] : []);
        $enumBacked = function (array $vals, string $enum): array {
            return array_values(array_filter(array_map(function ($v) use ($enum) {
                if ($v instanceof $enum) {
                    return $v->value;
                }
                if (($e = $enum::tryFrom($v))) {
                    return $e->value;
                }   // backed value
                // allow passing enum NAME like "A_POS"
                if (is_string($v) && defined("$enum::$v")) {
                    $const = constant("$enum::$v");

                    return $const instanceof $enum ? $const->value : null;
                }

                return is_scalar($v) ? $v : null; // last resort
            }, $vals), fn ($v) => $v !== null && $v !== ''));
        };

        // text
        $q->when($f['name'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"));

        // nationality_id (single or array)
        if ($ids = $toArray($f['nationality_id'] ?? null)) {
            $q->where('nationality_id', $ids);
        }

        // age range
        $min = $f['min_age'] ?? null;
        $max = $f['max_age'] ?? null;
        if ($min !== null) {
            $q->where('age', '>=', $min);
        }
        if ($max !== null) {
            $q->where('age', '<=', $max);
        }
        // exact age still supported
        $q->when($f['age'] ?? null, fn ($q, $v) => $q->where('age', $v));

        // enums (except single value or array → do whereIn)
        if ($vals = $enumBacked($toArray($f['blood_type'] ?? null), BloodType::class)) {
            $q->whereIn('blood_type', $vals);
        }
        if ($vals = $enumBacked($toArray($f['gender'] ?? null), Gender::class)) {
            $q->where('gender', $vals);
        }
        if ($vals = $enumBacked($toArray($f['marital_status'] ?? null), MaritalStatus::class)) {
            $q->whereIn('marital_status', $vals);
        }
        if ($vals = $enumBacked($toArray($f['is_active'] ?? null), ActiveEnum::class)) {
            $q->where('is_active', $vals);
        }

        return $q;
    }

    public function medicalExamination(): HasMany
    {
        return $this
            ->hasMany(MedicalExamination::class, 'patient_id'); // optional
    }

    public function getFinalDiagnosisNamesAttribute()
    {
        return $this
            ->finalDiagnosis()
            ->select('final_diagnoses.name', 'final_diagnoses.id')
            ->distinct()
            ->pluck('final_diagnoses.name');
    }

    public function finalDiagnosis(): BelongsToMany
    {
        return $this
            ->belongsToMany(FinalDiagnosis::class, 'final_diagnosis_patients')
            ->using(FinalDiagnosisPatient::class); // optional
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
     * Get the social accounts for the patient.
     */
    public function socialAccounts()
    {
        return $this->morphMany(SocialAccount::class, 'user');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new PatientVerifyEmailNotification);
    }
}
