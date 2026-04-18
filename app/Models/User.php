<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Auth\app\Models\SocialAccount;
use Modules\Core\App\Enums\Gender;
use Modules\Core\app\Models\Address;
use Modules\Mps\Models\Commission;
use Modules\Mps\Models\Company;
use Modules\Mps\Models\Iban;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property mixed $name
 * @property mixed $surname
 * @property mixed $email
 * @property mixed $password
 * @property mixed $phone
 * @property mixed $authority_identity
 * @property mixed $status
 * @property mixed $gender
 * @property mixed $birthdate
 * @property mixed $created_by
 * @property mixed $updated_by
 * @property mixed $deleted_by
 * @property mixed $action_by
 * @property mixed $tax_number
 * @property mixed $delegate
 * @property mixed $is_login
 * @property mixed $confirm
 * @property mixed $password_updated_date
 * @property mixed $password_expiry_date
 * @property mixed $password_period
 * @property mixed $kvkk_verify
 * @property mixed $company_image_verified
 * @property mixed $security_picture
 * @property mixed $verify_picture
 * @property mixed $is_block
 * @property mixed $attempts
 * @property mixed $user_point
 * @property mixed $company_id
 * @property null|Commission $commissions
 * @property mixed|string $img
 * @property Address|null $address
 * @property Company|null $company
 * @property Iban|null $iban
 */
class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, InteractsWithMedia, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'phone',
        'authority_identity',
        'status',
        'gender',
        'birthdate',
        'created_by',
        'updated_by',
        'deleted_by',
        'action_by',
        'tax_number',
        'delegate',
        'is_login',
        'confirm',
        'password_updated_date',
        'password_expiry_date',
        'password_period',
        'kvkk_verify',
        'company_image_verified',
        'security_picture',
        'verify_picture',
        'is_block',
        'attempts',
        'user_point',
        'company_id',
        'img',
        'email_verified_at',
    ];

    protected $casts = [
        'gender' => Gender::class,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getFullNameAttribute(): string
    {
        return "$this->name $this->surname";
    }

    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function scopeFilter($query, $filters)
    {
        return $query
            ->when(
                ! empty($filters['name']),
                fn ($q) => $q->where('name', 'like', '%'.$filters['name'].'%'),
            )
            ->when(
                ! empty($filters['company_type']),
                fn ($q) => $q->whereHas('company', function ($q) use ($filters) {
                    $q->where('company_type', $filters['company_type']);
                }),
            )
            ->when(
                ! empty($filters['delegate']),
                fn ($q) => $q->whereHas('company', function ($q) use ($filters) {
                    $q->where('delegate', $filters['delegate']);
                }),
            )
            ->when(
                ! empty($filters['city_id']),
                fn ($q) => $q->whereHas('address', function ($q) use ($filters) {
                    $q->where('city_id', $filters['city_id']);
                }),
            )
            ->when(
                ! empty($filters['status']),
                fn ($q) => $q->where('status', $filters['status']),
            );
    }

    /**
     * Get the social accounts for the user.
     */
    public function socialAccounts()
    {
        return $this->morphMany(SocialAccount::class, 'user');
    }
}
