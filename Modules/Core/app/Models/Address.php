<?php

namespace Modules\Core\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\app\Enums\PrimaryAddressStatus;

class Address extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'country_id',
        'addressable_id',
        'addressable_type',
        'city_id',
        'state_id',
        'full_address',
        'latitude',
        'longitude',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => PrimaryAddressStatus::class,
        'country_id' => 'int',
        'city_id' => 'int',
        'state_id' => 'int',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }
}
