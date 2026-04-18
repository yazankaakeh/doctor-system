<?php

namespace Modules\Core\app\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'country_id'];
}
