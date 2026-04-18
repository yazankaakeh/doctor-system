<?php

namespace Modules\Website\Livewire\Panels;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class CtaPanel extends Component
{
    public $panel;

    public function render(): View
    {
        return view('website::newLanding.panels.landingCTA', [
            'panel' => $this->panel,
            'items' => $this->panel['items'] ?? collect(),
        ]);
    }
}
