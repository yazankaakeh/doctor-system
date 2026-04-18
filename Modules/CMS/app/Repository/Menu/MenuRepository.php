<?php

namespace Modules\CMS\Repository\Menu;

use App\Enum\Pagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\CMS\Models\Menu;
use Modules\CMS\Models\MenuItem;

class MenuRepository implements MenuInterface
{
    public function index(): LengthAwarePaginator
    {
        return Menu::query()
            ->withCount('allItems')
            ->orderBy('created_at', 'desc')
            ->paginate(Pagination::PAG->value);
    }

    public function store(Request $request): Menu
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated) {
            $menu = new Menu;
            $menu->name = $validated['name'];
            $menu->slug = $validated['slug'];
            $menu->location = $validated['location'] ?? null;
            $menu->is_active = $validated['is_active'] ?? true;
            $menu->save();

            if (! empty($validated['items']) && is_array($validated['items'])) {
                $this->syncMenuItems($menu, $validated['items']);
            }

            return $menu->load('items');
        });
    }

    public function update(int $id, Request $request): Menu
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($id, $validated) {
            $menu = $this->find($id);
            $menu->name = $validated['name'];
            $menu->slug = $validated['slug'];
            $menu->location = $validated['location'] ?? null;
            $menu->is_active = $validated['is_active'] ?? true;
            $menu->save();

            if (array_key_exists('items', $validated)) {
                // Delete existing items
                $menu->allItems()->delete();

                // Create new items
                if (! empty($validated['items']) && is_array($validated['items'])) {
                    $this->syncMenuItems($menu, $validated['items']);
                }
            }

            return $menu->load('items');
        });
    }

    private function syncMenuItems(Menu $menu, array $items): void
    {
        foreach ($items as $itemData) {
            $menuItem = new MenuItem;
            $menuItem->menu_id = $menu->id;
            $menuItem->parent_id = $itemData['parent_id'] ?? null;
            $menuItem->title = $itemData['title'];
            $menuItem->url = $itemData['url'] ?? null;
            $menuItem->target = $itemData['target'] ?? '_self';
            $menuItem->page_id = $itemData['page_id'] ?? null;
            $menuItem->icon = $itemData['icon'] ?? null;
            $menuItem->css_class = $itemData['css_class'] ?? null;
            $menuItem->order = $itemData['order'] ?? 0;
            $menuItem->is_active = $itemData['is_active'] ?? true;
            $menuItem->save();
        }
    }

    public function find(int $id): Menu
    {
        return Menu::query()->with(['items'])->findOrFail($id);
    }

    public function destroy(int $id): void
    {
        $menu = $this->find($id);
        $menu->delete();
    }
}
