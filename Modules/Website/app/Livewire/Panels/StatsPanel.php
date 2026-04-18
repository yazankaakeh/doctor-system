<?php

namespace Modules\Website\Livewire\Panels;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class StatsPanel extends Component
{
    public $panel;

    public function render(): View
    {
        return view('website::newLanding.panels.landingFunFacts', [
            'panel' => $this->panel,
            'items' => $this->panel['items'] ?? collect(),
        ]);
    }
}
