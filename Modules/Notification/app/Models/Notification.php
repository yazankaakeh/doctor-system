<?php

namespace Modules\Notification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'notifiable_id',
        'notifiable_type',
        'type',
        'read',
        'action_key',
        'action_value',
        'data',
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
