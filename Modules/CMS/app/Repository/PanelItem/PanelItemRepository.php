<?php

namespace Modules\CMS\Repository\PanelItem;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\CMS\Enums\PanelItemTypeEnum;
use Modules\CMS\Models\PanelItem;

class PanelItemRepository implements PanelItemInterface
{
    public function getByPanel(int $panelId): Collection
    {
        return PanelItem::query()
            ->byPanel($panelId)
            ->ordered()
            ->get();
    }

    public function getActiveByPanel(int $panelId): Collection
    {
        return PanelItem::query()
            ->byPanel($panelId)
            ->active()
            ->ordered()
            ->get();
    }

    public function find(int $id): ?PanelItem
    {
        return PanelItem::find($id);
    }

    public function store(Request $request): PanelItem
    {
        $data = $request->validated();

        // Get next order if not provided
        if (! isset($data['order'])) {
            $data['order'] = $this->getNextOrder($data['panel_id']);
        }

        // Create item
        $item = PanelItem::create($data);

        // Handle image upload if present
        if ($request->hasFile('item_image')) {
            $item->addMediaFromRequest('item_image')
                ->toMediaCollection('item_image');
        }

        return $item->fresh();
    }

    public function update(int $id, Request $request): PanelItem
    {
        $item = $this->find($id);

        if (! $item) {
            abort(404, 'Panel item not found');
        }

        $data = $request->validated();
        $item->update($data);

        // Handle image upload if present
        if ($request->hasFile('item_image')) {
            $item->clearMediaCollection('item_image');
            $item->addMediaFromRequest('item_image')
                ->toMediaCollection('item_image');
        }

        return $item->fresh();
    }

    public function destroy(int $id): void
    {
        $item = $this->find($id);

        if ($item) {
            $item->delete();
        }
    }

    public function reorder(array $orderData): void
    {
        foreach ($orderData as $order => $id) {
            PanelItem::where('id', $id)->update(['order' => $order]);
        }
    }

    public function duplicate(int $id): PanelItem
    {
        $original = $this->find($id);

        if (! $original) {
            abort(404, 'Panel item not found');
        }

        // Create duplicate
        $duplicate = $original->replicate();
        $duplicate->title = array_map(
            fn ($title) => $title ? $title.' (Copy)' : null,
            $original->title ?? []
        );
        $duplicate->order = $this->getNextOrder($original->panel_id);
        $duplicate->is_active = false; // Deactivate by default
        $duplicate->save();

        // Duplicate media
        foreach ($original->getMedia('item_image') as $media) {
            $media->copy($duplicate, 'item_image');
        }

        return $duplicate;
    }

    public function toggleStatus(int $id): PanelItem
    {
        $item = $this->find($id);

        if (! $item) {
            abort(404, 'Panel item not found');
        }

        $item->is_active = ! $item->is_active;
        $item->save();

        return $item;
    }

    public function bulkDelete(array $ids): void
    {
        PanelItem::whereIn('id', $ids)->delete();
    }

    public function getByType(PanelItemTypeEnum $type): Collection
    {
        return PanelItem::byType($type)->ordered()->get();
    }

    public function getNextOrder(int $panelId): int
    {
        $maxOrder = PanelItem::byPanel($panelId)->max('order');

        return ($maxOrder ?? -1) + 1;
    }

    public function bulkUpdateOrder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                PanelItem::whereKey($id)->update(['order' => $index]);
            }
        });
    }
}
