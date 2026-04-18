<?php

namespace Modules\Website\Livewire\Panels;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ContactPanel extends Component
{
    public $panel;

    public function render(): View
    {
        return view('website::newLanding.panels.landingContact', [
            'panel' => $this->panel,
            'items' => $this->panel['items'] ?? collect(),
        ]);
    }
}
