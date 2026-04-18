<?php

namespace Modules\CMS\Actions\Panel;

use Modules\CMS\Repository\Panel\PanelInterface;

class DeletePanelAction
{
    public function __construct(
        private readonly PanelInterface $panelRepository
    ) {}

    public function handle(int $id): void
    {
        $this->panelRepository->destroy($id);
    }
}
