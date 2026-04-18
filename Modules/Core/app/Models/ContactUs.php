<?php

namespace Modules\Core\app\Models;

use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'fullName',
        'email',
        'message',
    ];
}
