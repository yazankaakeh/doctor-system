<?php

namespace Modules\CMS\Actions\Panel;

use Illuminate\Database\Eloquent\Collection;
use Modules\CMS\Repository\Panel\PanelInterface;

class GetPanelsForPageAction
{
    public function __construct(
        private readonly PanelInterface $panelRepository
    ) {}

    public function handle(int $pageId, bool $activeOnly = false): Collection
    {
        if ($activeOnly) {
            return $this->panelRepository->getActiveByPage($pageId);
        }

        return $this->panelRepository->getByPage($pageId);
    }
}
