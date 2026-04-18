<?php

namespace Modules\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\CMS\Http\Requests\PortfolioRequest;
use Modules\CMS\Repository\Portfolio\PortfolioInterface;

class PortfolioController extends Controller
{
    public function __construct(
        private readonly PortfolioInterface $portfolioRepository
    ) {}

    /**
     * Display a listing of portfolios.
     */
    public function index(): View
    {
        $portfolios = $this->portfolioRepository->index(15);

        return view('cms::portfolio.index', compact('portfolios'));
    }

    /**
     * Show the form for creating a new portfolio.
     */
    public function create(): View
    {
        $categories = $this->portfolioRepository->getCategories();

        return view('cms::portfolio.create', compact('categories'));
    }

    /**
     * Store a newly created portfolio.
     */
    public function store(PortfolioRequest $request): RedirectResponse
    {
        $portfolio = $this->portfolioRepository->store($request);

        return redirect()
            ->route('admin.portfolios.edit', $portfolio->id)
            ->with('success', 'Portfolio created successfully.');
    }

    /**
     * Display the specified portfolio.
     */
    public function show(int $id): View
    {
        $portfolio = $this->portfolioRepository->find($id);

        if (! $portfolio) {
            abort(404, 'Portfolio not found');
        }

        return view('cms::portfolio.show', compact('portfolio'));
    }

    /**
     * Show the form for editing the specified portfolio.
     */
    public function edit(int $id): View
    {
        $portfolio = $this->portfolioRepository->find($id);

        if (! $portfolio) {
            abort(404, 'Portfolio not found');
        }

        $categories = $this->portfolioRepository->getCategories();

        return view('cms::portfolio.edit', compact('portfolio', 'categories'));
    }

    /**
     * Update the specified portfolio.
     */
    public function update(PortfolioRequest $request, int $id): RedirectResponse
    {
        $this->portfolioRepository->update($id, $request);

        return redirect()
            ->route('admin.portfolios.edit', $id)
            ->with('success', 'Portfolio updated successfully.');
    }

    /**
     * Remove the specified portfolio.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->portfolioRepository->destroy($id);

        return redirect()
            ->route('admin.portfolios.index')
            ->with('success', 'Portfolio deleted successfully.');
    }

    /**
     * Toggle portfolio status.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $portfolio = $this->portfolioRepository->toggleStatus($id);

        return response()->json([
            'success' => true,
            'is_active' => $portfolio->is_active,
            'message' => $portfolio->is_active ? 'Portfolio activated.' : 'Portfolio deactivated.',
        ]);
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(int $id): JsonResponse
    {
        $portfolio = $this->portfolioRepository->toggleFeatured($id);

        return response()->json([
            'success' => true,
            'is_featured' => $portfolio->is_featured,
            'message' => $portfolio->is_featured ? 'Portfolio marked as featured.' : 'Portfolio unmarked as featured.',
        ]);
    }

    /**
     * Duplicate portfolio.
     */
    public function duplicate(int $id): RedirectResponse
    {
        $portfolio = $this->portfolioRepository->duplicate($id);

        return redirect()
            ->route('admin.portfolios.edit', $portfolio->id)
            ->with('success', 'Portfolio duplicated successfully.');
    }

    /**
     * Reorder portfolios.
     */
    public function reorder(): JsonResponse
    {
        $orderData = request()->input('order', []);
        $this->portfolioRepository->reorder($orderData);

        return response()->json([
            'success' => true,
            'message' => 'Portfolios reordered successfully.',
        ]);
    }

    /**
     * Delete gallery image.
     */
    public function deleteGalleryImage(int $id, int $mediaId): JsonResponse
    {
        $portfolio = $this->portfolioRepository->find($id);

        if (! $portfolio) {
            return response()->json(['success' => false, 'message' => 'Portfolio not found'], 404);
        }

        $media = $portfolio->getMedia('gallery')->where('id', $mediaId)->first();

        if (! $media) {
            return response()->json(['success' => false, 'message' => 'Image not found'], 404);
        }

        $media->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully.',
        ]);
    }
}
