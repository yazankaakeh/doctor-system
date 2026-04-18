<?php

namespace Modules\Seo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

class SeoMeta extends Model
{
    use HasTranslations;

    public $table = 'seo_meta';

    public array $translatable = [
        'title',
        'meta_description',
        'og',
        'twitter',
        'jsonld',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'seoable_id',
        'seoable_type',
        'title',
        'meta_description',
        'canonical_url',
        'robots_index',
        'robots_follow',
        'og',
        'twitter',
        'jsonld',
    ];

    protected $casts = [
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
        'og' => 'array',
        'twitter' => 'array',
        'jsonld' => 'array',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
