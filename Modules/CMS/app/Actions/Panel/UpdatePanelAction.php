<?php

namespace Modules\CMS\Actions\Panel;

use Illuminate\Http\Request;
use Modules\CMS\Models\Panel;
use Modules\CMS\Repository\Panel\PanelInterface;

class UpdatePanelAction
{
    public function __construct(
        private readonly PanelInterface $panelRepository
    ) {}

    public function handle(int $id, Request $request): Panel
    {
        return $this->panelRepository->update($id, $request);
    }
}
