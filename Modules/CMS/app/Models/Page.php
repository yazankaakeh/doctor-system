<?php

namespace Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CMS\Enums\PageStatusEnum;
use Modules\CMS\Enums\PageTemplateEnum;
use Modules\Seo\Traits\HasSeo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\Translatable\HasTranslations;

class Page extends Model implements HasMedia
{
    use HasSeo, HasTranslations, InteractsWithMedia, SoftDeletes;

    public array $translatable = [
        'title',
        'content',
        'excerpt',
    ];

    protected $table = 'cms_pages';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'template',
        'use_panel_builder',
        'parent_id',
        'order',
        'meta_data',
        'published_at',
    ];

    protected $casts = [
        'title' => 'array',
        'content' => 'array',
        'excerpt' => 'array',
        'meta_data' => 'array',
        'status' => PageStatusEnum::class,
        'template' => PageTemplateEnum::class,
        'use_panel_builder' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('featured_image')
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ], true);
            })->singleFile();

        $this
            ->addMediaCollection('gallery')
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ], true);
            });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }

    public function panels(): HasMany
    {
        return $this->hasMany(Panel::class)->orderBy('order');
    }

    public function activePanels(): HasMany
    {
        return $this->hasMany(Panel::class)->where('is_active', true)->orderBy('order');
    }

    public function isPublished(): bool
    {
        return $this->status === PageStatusEnum::PUBLISHED
            && $this->published_at !== null
            && $this->published_at->isPast();
    }

    public function scopePublished($query)
    {
        return $query->where('status', PageStatusEnum::PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->whereLike('slug', $slug);
    }
}
