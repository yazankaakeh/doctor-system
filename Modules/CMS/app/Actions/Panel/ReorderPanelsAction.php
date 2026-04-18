<?php

namespace Modules\CMS\Actions\Panel;

use Modules\CMS\Repository\Panel\PanelInterface;

class ReorderPanelsAction
{
    public function __construct(
        private readonly PanelInterface $panelRepository
    ) {}

    public function handle(array $orderData): void
    {
        $this->panelRepository->reorder($orderData);
    }
}
