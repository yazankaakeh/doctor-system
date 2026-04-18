<?php

namespace Modules\CMS\Actions\Page;

use Modules\CMS\Models\Page;
use Modules\CMS\Repository\Page\PageInterface;

class GetHomePageAction
{
    public function __construct(
        private readonly PageInterface $pageRepository
    ) {}

    public function handle(): ?Page
    {
        return $this->pageRepository->getHomePage();
    }

    public function handleOrFail(): Page
    {
        $homePage = $this->handle();

        if (! $homePage) {
            abort(404, 'Home page not found. Please run: php artisan db:seed --class=Modules\\CMS\\Database\\Seeders\\LandingPageSeeder');
        }

        return $homePage;
    }
}
