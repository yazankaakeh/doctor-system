<?php

namespace Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CMS\Enums\PanelItemTypeEnum;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\Translatable\HasTranslations;

class PanelItem extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $table = 'cms_panel_items';

    public array $translatable = [
        'title',
        'content',
    ];

    protected $fillable = [
        'panel_id',
        'type',
        'title',
        'content',
        'order',
        'is_active',
        'data',
    ];

    protected $casts = [
        'title' => 'array',
        'content' => 'array',
        'type' => PanelItemTypeEnum::class,
        'is_active' => 'boolean',
        'data' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('item_image')
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ], true);
            })->singleFile();

        $this
            ->addMediaCollection('item_gallery')
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ], true);
            });
    }

    public function panel(): BelongsTo
    {
        return $this->belongsTo(Panel::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeByType($query, PanelItemTypeEnum $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPanel($query, int $panelId)
    {
        return $query->where('panel_id', $panelId);
    }

    /**
     * Get a specific data value from the JSON data column
     */
    public function getData(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Set a specific data value in the JSON data column
     */
    public function setData(string $key, $value): void
    {
        $data = $this->data ?? [];
        $data[$key] = $value;
        $this->data = $data;
    }
}
