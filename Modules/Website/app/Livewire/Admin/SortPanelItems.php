<?php

namespace Modules\Website\Livewire\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\CMS\Models\PanelItem;
use Modules\CMS\Repository\PanelItem\PanelItemInterface;

class SortPanelItems extends Component
{
    public int $panelId;

    public $items = [];

    public function mount(int $panelId, PanelItemInterface $panelItemRepository): void
    {
        $this->panelId = $panelId;
        $this->loadItems($panelItemRepository);
    }

    public function loadItems(PanelItemInterface $panelItemRepository): void
    {
        $this->items = $panelItemRepository->getByPanel($this->panelId)->map(function (PanelItem $item) {
            return [
                'id' => $item->id,
                'title' => $item->title[app()->getLocale()] ?? 'Untitled',
                'type' => $item->type,
                'is_active' => $item->is_active,
                'order' => $item->order,
            ];
        })->sortBy('order')->values();
    }

    #[On('panelItemsReordered')]
    public function panelItemsReordered(int $panelId, array $ids): void
    {
        if ($this->panelId !== $panelId) {
            return;
        }

        $this->bulkUpdateOrder($ids);
        $this->loadItems(app(PanelItemInterface::class));
    }

    public function bulkUpdateOrder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                PanelItem::whereKey($id)->update(['order' => $index]);
            }
        });
    }

    public function render(): View
    {
        return view('website::livewire.admin.sort-panel-items')
            ->layout('website::livewire.admin.layout');
    }
}
