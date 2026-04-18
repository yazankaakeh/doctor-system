<?php

namespace Modules\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Seo\Traits\HasSeo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class BlogPostTags extends Model implements HasMedia
{
    use HasSeo, HasTranslations, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     */
    public array $translatable = [
        'name',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'name' => 'array',
    ];
}
