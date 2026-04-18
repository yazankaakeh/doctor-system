<?php

namespace Modules\AdminManagement\app\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\AdminManagement\Enums\ActiveAdminEnum;
use Modules\Auth\Models\SocialAccount;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property mixed $name
 * @property mixed $email
 * @property mixed|string $password
 * @property mixed|string $img
 * @property int|mixed $is_active
 * @property mixed $phone
 *
 * @method addMedia(string $string)
 */
class Admin extends Authenticatable
{
    use HasRoles;

    protected string $guard = 'admin';

    protected $table = 'admins';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => ActiveAdminEnum::class,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'img',
        'is_active',
    ];

    /**
     * Get the social accounts for the admin.
     */
    public function socialAccounts()
    {
        return $this->morphMany(SocialAccount::class, 'user');
    }
}
