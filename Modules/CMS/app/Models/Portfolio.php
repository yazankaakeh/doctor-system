<?php

namespace Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Seo\Traits\HasSeo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class Portfolio extends Model implements HasMedia
{
    use HasSeo, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $table = 'portfolios';

    public array $translatable = [
        'title',
        'short_description',
        'description',
        'category',
        'features',
        'challenges',
        'solutions',
        'results',
        'testimonial',
    ];

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'description',
        'category',
        'client_name',
        'client_company',
        'project_url',
        'technologies',
        'start_date',
        'completion_date',
        'project_budget',
        'project_duration',
        'features',
        'challenges',
        'solutions',
        'results',
        'testimonial',
        'testimonial_author',
        'testimonial_position',
        'is_featured',
        'is_active',
        'order',
        'meta_data',
        'views_count',
    ];

    protected $casts = [
        'title' => 'array',
        'short_description' => 'array',
        'description' => 'array',
        'category' => 'array',
        'technologies' => 'array',
        'features' => 'array',
        'challenges' => 'array',
        'solutions' => 'array',
        'results' => 'array',
        'testimonial' => 'array',
        'meta_data' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'completion_date' => 'date',
        'project_budget' => 'decimal:2',
        'views_count' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($portfolio) {
            if (empty($portfolio->slug)) {
                $title = is_array($portfolio->title)
                    ? ($portfolio->title['en'] ?? reset($portfolio->title))
                    : $portfolio->title;
                $portfolio->slug = Str::slug($title);
            }

            // Ensure unique slug
            $originalSlug = $portfolio->slug;
            $count = 1;
            while (static::where('slug', $portfolio->slug)->exists()) {
                $portfolio->slug = $originalSlug.'-'.$count++;
            }

            // Set order if not provided
            if ($portfolio->order === null || $portfolio->order === 0) {
                $portfolio->order = static::max('order') + 1;
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('featured_image')
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'image/gif',
                ], true);
            })
            ->singleFile();

        $this
            ->addMediaCollection('gallery')
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'image/gif',
                ], true);
            });

        $this
            ->addMediaCollection('logo')
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'image/svg+xml',
                ], true);
            })
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('featured_image', 'gallery');

        $this->addMediaConversion('medium')
            ->width(800)
            ->height(600)
            ->sharpen(10)
            ->performOnCollections('featured_image', 'gallery');

        $this->addMediaConversion('large')
            ->width(1200)
            ->height(900)
            ->sharpen(10)
            ->performOnCollections('featured_image', 'gallery');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', 'like', '%"'.$category.'"%');
    }

    // Accessors
    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('featured_image') ?: null;
    }

    public function getFeaturedImageThumbAttribute(): ?string
    {
        return $this->getFirstMediaUrl('featured_image', 'thumb') ?: null;
    }

    public function getGalleryImagesAttribute(): array
    {
        return $this->getMedia('gallery')->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb' => $media->getUrl('thumb'),
                'medium' => $media->getUrl('medium'),
                'large' => $media->getUrl('large'),
                'name' => $media->name,
                'file_name' => $media->file_name,
            ];
        })->toArray();
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('logo') ?: null;
    }

    public function getTechnologiesListAttribute(): array
    {
        return $this->technologies ?? [];
    }

    public function getShareUrlAttribute(): string
    {
        return route('portfolio.show', $this->slug);
    }

    // Methods
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function getShareLinks(): array
    {
        $url = urlencode($this->share_url);
        $title = urlencode($this->getTranslation('title', app()->getLocale()));

        return [
            'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$url}",
            'twitter' => "https://twitter.com/intent/tweet?url={$url}&text={$title}",
            'linkedin' => "https://www.linkedin.com/sharing/share-offsite/?url={$url}",
            'whatsapp' => "https://wa.me/?text={$title}%20{$url}",
            'telegram' => "https://t.me/share/url?url={$url}&text={$title}",
            'pinterest' => "https://pinterest.com/pin/create/button/?url={$url}&description={$title}",
            'email' => "mailto:?subject={$title}&body={$url}",
        ];
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['featured_image_url'] = $this->featured_image_url;
        $array['featured_image_thumb'] = $this->featured_image_thumb;
        $array['gallery_images'] = $this->gallery_images;
        $array['logo_url'] = $this->logo_url;
        $array['share_url'] = $this->share_url;
        $array['share_links'] = $this->getShareLinks();

        return $array;
    }
}
