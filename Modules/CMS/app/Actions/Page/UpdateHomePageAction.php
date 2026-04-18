<?php

namespace Modules\CMS\Actions\Page;

use Illuminate\Http\Request;
use Modules\CMS\Models\Page;
use Modules\CMS\Repository\Page\PageInterface;

class UpdateHomePageAction
{
    public function __construct(
        private readonly PageInterface $pageRepository
    ) {}

    public function handle(Request $request): Page
    {
        $homePage = $this->pageRepository->getHomePage();

        if (! $homePage) {
            throw new \Exception('Home page not found.');
        }

        return $this->pageRepository->update($homePage->id, $request);
    }
}
