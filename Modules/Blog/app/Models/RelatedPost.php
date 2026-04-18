<?php

namespace Modules\Blog\Models;

use Illuminate\Database\Eloquent\Model;

class RelatedPost extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'post_id',
        'related_post_id',
    ];
}
