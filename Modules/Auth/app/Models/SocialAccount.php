<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SocialAccount extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'user_type',
        'provider',
        'provider_user_id',
        'token',
        'refresh_token',
        'expires_in',
    ];

    /**
     * Get the user that owns the social account.
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user type for the social account.
     */
    public function getUserTypeAttribute(): string
    {
        return $this->user_type;
    }

    /**
     * Scope to filter by provider.
     */
    public function scopeProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope to filter by provider user ID.
     */
    public function scopeProviderUserId($query, string $providerUserId)
    {
        return $query->where('provider_user_id', $providerUserId);
    }
}
