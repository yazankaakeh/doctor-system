<?php

namespace Modules\Website\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Modules\CMS\Repository\Panel\PanelInterface;
use Modules\Website\PanelBuilder;

class NewLandingPage extends Component
{
    public $panels;

    public function mount(PanelInterface $panelRepository, PanelBuilder $panelBuilder): void
    {
        // Get active panels ordered by their order field
        $rawPanels = $panelRepository->getActiveByPage(1); // Assuming page ID 1 for landing page

        // Use PanelBuilder to transform data for presentation
        $this->panels = $panelBuilder->build($rawPanels);
    }

    public function render(): View
    {
        return view('website::livewire.new-landing-page');
    }
}
