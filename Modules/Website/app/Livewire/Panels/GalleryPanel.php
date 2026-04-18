<?php

namespace Modules\Website\Livewire\Panels;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class GalleryPanel extends Component
{
    public $panel;

    public function render(): View
    {
        return view('website::newLanding.panels.gallery', [
            'panel' => $this->panel,
            'items' => $this->panel['items'] ?? collect(),
        ]);
    }
}
