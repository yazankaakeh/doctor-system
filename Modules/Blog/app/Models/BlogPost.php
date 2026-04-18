<?php

namespace Modules\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Blog\Enums\PostTypeEnum;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Seo\Traits\HasSeo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\Translatable\HasTranslations;

/**
 * @property mixed $category_id
 */
class BlogPost extends Model implements HasMedia
{
    use HasSeo, HasTranslations, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     */
    public array $translatable = [
        'title',
        'description',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'category_id',
        'authorable_id',
        'authorable_type',
        'title',
        'description',
        'clapping',
        'type',
        'is_active',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'type' => PostTypeEnum::class,
        'is_active' => ActiveEnum::class,
    ];

    public function category(): HasOne
    {
        return $this->hasOne(BlogCategory::class, 'id', 'category_id');
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('img')
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ], true);
            })->singleFile();
    }

    public function tags(): BelongsToMany
    {
        return $this
            ->belongsToMany(BlogPostTags::class, 'blog_post_tags_posts', 'post_id', 'tag_id')
            ->using(BlogPostTagsPosts::class);
    }

    public function relatedPosts(): BelongsToMany
    {
        return $this
            ->belongsToMany(self::class, 'related_posts', 'post_id', 'related_post_id')
            ->withPivot(['position', 'relation_type'])
            ->orderBy('position');
    }

    public function relatedOfPosts(): BelongsToMany
    {
        return $this
            ->belongsToMany(self::class, 'related_posts', 'related_post_id', 'post_id')
            ->withPivot(['position', 'relation_type'])
            ->orderBy('position');
    }

    public function allRelatedPosts()
    {
        $a = $this->relatedPosts;
        $b = $this->relatedOfPosts;

        return $a->merge($b)->unique('id')->values();
    }

    public function totalClaps(): int
    {
        return $this->claps()->sum('clap_count');
    }

    public function claps(): HasMany
    {
        return $this->hasMany(BlogPostClap::class, 'post_id');
    }
}
