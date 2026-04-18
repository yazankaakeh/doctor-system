<?php

namespace Modules\Messaging\Livewire;

use Livewire\Component;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\ConversationStatusEnum;

class ConversationFilters extends Component
{
    public ?string $channelFilter = null;

    public ?string $statusFilter = null;

    public ?int $assignedFilter = null;

    public function updatedChannelFilter(): void
    {
        $this->dispatch('filters-updated', [
            'channel' => $this->channelFilter,
            'status' => $this->statusFilter,
            'assigned' => $this->assignedFilter,
        ]);
    }

    public function updatedStatusFilter(): void
    {
        $this->dispatch('filters-updated', [
            'channel' => $this->channelFilter,
            'status' => $this->statusFilter,
            'assigned' => $this->assignedFilter,
        ]);
    }

    public function updatedAssignedFilter(): void
    {
        $this->dispatch('filters-updated', [
            'channel' => $this->channelFilter,
            'status' => $this->statusFilter,
            'assigned' => $this->assignedFilter,
        ]);
    }

    public function clearFilters(): void
    {
        $this->channelFilter = null;
        $this->statusFilter = null;
        $this->assignedFilter = null;

        $this->dispatch('filters-updated', [
            'channel' => null,
            'status' => null,
            'assigned' => null,
        ]);
    }

    public function render()
    {
        return view('messaging::livewire.conversation-filters', [
            'channelOptions' => collect(ChannelTypeEnum::cases())
                ->mapWithKeys(fn ($c) => [$c->value => $c->label()])
                ->toArray(),
            'statusOptions' => collect(ConversationStatusEnum::cases())
                ->mapWithKeys(fn ($s) => [$s->value => $s->label()])
                ->toArray(),
        ]);
    }
}
