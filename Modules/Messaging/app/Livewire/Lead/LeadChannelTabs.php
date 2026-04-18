<?php

namespace Modules\Messaging\Livewire\Lead;

use Livewire\Component;

class LeadChannelTabs extends Component
{
    public array $channels = [];

    public ?string $selectedChannel = null;

    public function mount(array $channels, ?string $selectedChannel = null): void
    {
        $this->channels = $channels;
        $this->selectedChannel = $selectedChannel;
    }

    public function selectChannel(string $channelType): void
    {
        $this->selectedChannel = $channelType;
        $this->dispatch('lead-channel-selected', channelType: $channelType);
    }

    public function render()
    {
        return view('messaging::livewire.lead.lead-channel-tabs');
    }
}
