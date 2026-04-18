<?php

namespace Modules\Core\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ThirdPartyLog extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'loggable_id',
        'loggable_tye',
        'type',
        'body',
    ];

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }
}
