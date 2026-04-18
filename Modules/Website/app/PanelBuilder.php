<?php

namespace Modules\Website;

use Illuminate\Database\Eloquent\Collection;
use Modules\CMS\Models\Panel;
use Modules\CMS\Models\PanelItem;

class PanelBuilder
{
    /**
     * Build panels for presentation layer
     * Transforms raw Eloquent models into structured data for frontend rendering
     */
    public function build(Collection $panels): Collection
    {
        return $panels->map(function (Panel $panel) {
            return [
                'id' => $panel->id,
                'type' => $panel->type,
                'title' => $panel->title,
                'slug' => $panel->slug,
                'is_active' => $panel->is_active,
                'order' => $panel->order,
                'settings' => $panel->settings,
                'items' => $this->buildItems($panel->items),
                'media' => [
                    'panel_image' => $panel->getFirstMediaUrl('panel_image'),
                    'panel_gallery' => $panel->getMedia('panel_gallery')->map(fn ($media) => $media->getUrl()),
                ],
            ];
        });
    }

    /**
     * Build panel items for presentation
     */
    private function buildItems(Collection $items): Collection
    {
        return $items->map(function (PanelItem $item) {
            return [
                'id' => $item->id,
                'type' => $item->type,
                'title' => $item->title,
                'content' => $item->content,
                'is_active' => $item->is_active,
                'order' => $item->order,
                'data' => $item->data,
                'media' => [
                    'item_image' => $item->getFirstMediaUrl('item_image'),
                    'item_gallery' => $item->getMedia('item_gallery')->map(fn ($media) => $media->getUrl()),
                ],
            ];
        });
    }
}
