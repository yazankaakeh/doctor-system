<?php

namespace Modules\CMS\Repository\Panel;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\CMS\Enums\PanelTypeEnum;
use Modules\CMS\Models\Panel;

class PanelRepository implements PanelInterface
{
    public function getByPage(int $pageId): Collection
    {
        return Panel::query()
            ->with(['items' => fn ($q) => $q->ordered()])
            ->byPage($pageId)
            ->ordered()
            ->get();
    }

    public function getActiveByPage(int $pageId): Collection
    {
        return Panel::query()
            ->with(['activeItems' => fn ($q) => $q->ordered()])
            ->byPage($pageId)
            ->active()
            ->ordered()
            ->get();
    }

    public function find(int $id): ?Panel
    {
        return Panel::find($id);
    }

    public function findWithItems(int $id): ?Panel
    {
        return Panel::with(['items' => fn ($q) => $q->ordered()])->find($id);
    }

    public function store(Request $request): Panel
    {
        $data = $request->validated();

        // Get next order if not provided
        if (! isset($data['order'])) {
            $data['order'] = $this->getNextOrder($data['page_id']);
        }

        // Create panel
        $panel = Panel::create($data);

        // Handle image upload if present
        if ($request->hasFile('panel_image')) {
            $panel->addMediaFromRequest('panel_image')
                ->toMediaCollection('panel_image');
        }

        return $panel->fresh();
    }

    public function update(int $id, Request $request): Panel
    {
        $panel = $this->find($id);

        if (! $panel) {
            abort(404, 'Panel not found');
        }

        $data = $request->validated();
        $panel->update($data);

        // Handle image upload if present
        if ($request->hasFile('panel_image')) {
            $panel->clearMediaCollection('panel_image');
            $panel->addMediaFromRequest('panel_image')
                ->toMediaCollection('panel_image');
        }

        return $panel->fresh();
    }

    public function destroy(int $id): void
    {
        $panel = $this->find($id);

        if ($panel) {
            // Items will be cascade deleted
            $panel->delete();
        }
    }

    public function reorder(array $orderData): void
    {
        foreach ($orderData as $order => $id) {
            Panel::where('id', $id)->update(['order' => $order]);
        }
    }

    public function duplicate(int $id): Panel
    {
        $original = $this->findWithItems($id);

        if (! $original) {
            abort(404, 'Panel not found');
        }

        // Create duplicate panel
        $duplicate = $original->replicate();
        $duplicate->title = array_map(
            fn ($title) => $title.' (Copy)',
            $original->title
        );
        $duplicate->order = $this->getNextOrder($original->page_id);
        $duplicate->is_active = false; // Deactivate by default
        $duplicate->save();

        // Duplicate media
        foreach ($original->getMedia('panel_image') as $media) {
            $media->copy($duplicate, 'panel_image');
        }

        // Duplicate items
        foreach ($original->items as $item) {
            $duplicateItem = $item->replicate();
            $duplicateItem->panel_id = $duplicate->id;
            $duplicateItem->save();

            // Duplicate item media
            foreach ($item->getMedia('item_image') as $media) {
                $media->copy($duplicateItem, 'item_image');
            }
        }

        return $duplicate->load('items');
    }

    public function toggleStatus(int $id): Panel
    {
        $panel = $this->find($id);

        if (! $panel) {
            abort(404, 'Panel not found');
        }

        $panel->is_active = ! $panel->is_active;
        $panel->save();

        return $panel;
    }

    public function getByType(PanelTypeEnum $type): Collection
    {
        return Panel::byType($type)->ordered()->get();
    }

    public function getNextOrder(int $pageId): int
    {
        $maxOrder = Panel::byPage($pageId)->max('order');

        return ($maxOrder ?? -1) + 1;
    }

    public function bulkUpdateOrder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                Panel::whereKey($id)->update(['order' => $index]);
            }
        });
    }
}
