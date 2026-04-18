<?php

namespace Modules\Notification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationPushToken extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'notification_push_token';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'tokenable_id',
        'tokenable_type',
        'push_token',
        'platform',
    ];

    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }
}
