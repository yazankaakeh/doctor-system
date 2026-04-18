<?php

namespace Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CMS\Enums\PanelTypeEnum;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\Translatable\HasTranslations;

class Panel extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $table = 'cms_panels';

    public array $translatable = [
        'title',
    ];

    protected $fillable = [
        'page_id',
        'type',
        'title',
        'slug',
        'order',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'title' => 'array',
        'type' => PanelTypeEnum::class,
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('panel_image')
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ], true);
            })->singleFile();

        $this
            ->addMediaCollection('panel_gallery')
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ], true);
            });
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PanelItem::class)->orderBy('order');
    }

    public function activeItems(): HasMany
    {
        return $this->hasMany(PanelItem::class)->where('is_active', true)->orderBy('order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeByType($query, PanelTypeEnum $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPage($query, int $pageId)
    {
        return $query->where('page_id', $pageId);
    }

    /**
     * Get a specific setting value, optionally for a specific locale if it's translatable.
     */
    public function getSetting(string $key, ?string $locale = null): mixed
    {
        $settings = $this->settings ?? [];
        $value = $settings[$key] ?? null;

        // If locale is provided and value is an array (translatable), get the localized value
        if ($locale !== null && is_array($value)) {
            return $value[$locale] ?? $value[config('app.fallback_locale')] ?? null;
        }

        return $value;
    }

    /**
     * Set a specific setting value.
     */
    public function setSetting(string $key, mixed $value): self
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;

        return $this;
    }
}
