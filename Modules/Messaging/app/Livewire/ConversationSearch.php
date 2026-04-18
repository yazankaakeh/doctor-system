<?php

namespace Modules\Messaging\Livewire;

use Livewire\Component;

class ConversationSearch extends Component
{
    public string $query = '';

    public function updatedQuery(): void
    {
        $this->dispatch('search-updated', query: $this->query);
    }

    public function clear(): void
    {
        $this->query = '';
        $this->dispatch('search-updated', query: '');
    }

    public function render()
    {
        return view('messaging::livewire.conversation-search');
    }
}
