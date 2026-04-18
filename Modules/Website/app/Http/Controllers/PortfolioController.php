<?php

namespace Modules\Website\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Modules\CMS\Repository\Portfolio\PortfolioInterface;

class PortfolioController extends Controller
{
    public function __construct(
        private readonly PortfolioInterface $portfolioRepository
    ) {}

    /**
     * Display portfolio listing page.
     */
    public function index(Request $request): View
    {
        $locale = app()->getLocale();
        $perPage = 12;

        // Handle category filter
        $category = $request->get('category');
        $search = $request->get('search');

        if ($search) {
            $portfolios = $this->portfolioRepository->search($search, $perPage);
        } elseif ($category) {
            $portfolios = $this->portfolioRepository->getByCategory($category, $perPage);
        } else {
            $portfolios = $this->portfolioRepository->getActivePaginated($perPage);
        }

        $categories = $this->portfolioRepository->getCategories();
        $featuredPortfolios = $this->portfolioRepository->getFeatured(3);

        return view('website::portfolio.index', compact(
            'portfolios',
            'categories',
            'featuredPortfolios',
            'locale',
            'category',
            'search'
        ));
    }

    /**
     * Display single portfolio page.
     */
    public function show(string $slug): View
    {
        $portfolio = $this->portfolioRepository->findActiveBySlug($slug);

        if (! $portfolio) {
            abort(404, 'Portfolio not found');
        }

        // Increment view count
        $portfolio->incrementViews();

        $locale = app()->getLocale();
        $relatedPortfolios = $this->portfolioRepository->getRelated($portfolio, 4);
        $shareLinks = $portfolio->getShareLinks();

        return view('website::portfolio.show', compact(
            'portfolio',
            'relatedPortfolios',
            'shareLinks',
            'locale'
        ));
    }
}
