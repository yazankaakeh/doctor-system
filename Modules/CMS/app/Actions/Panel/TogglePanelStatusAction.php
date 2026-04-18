<?php

namespace Modules\CMS\Actions\Panel;

use Modules\CMS\Models\Panel;
use Modules\CMS\Repository\Panel\PanelInterface;

class TogglePanelStatusAction
{
    public function __construct(
        private readonly PanelInterface $panelRepository
    ) {}

    public function handle(int $id): Panel
    {
        return $this->panelRepository->toggleStatus($id);
    }
}
