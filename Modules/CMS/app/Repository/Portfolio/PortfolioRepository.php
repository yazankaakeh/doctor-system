<?php

namespace Modules\CMS\Repository\Portfolio;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\CMS\Models\Portfolio;
use Throwable;

class PortfolioRepository implements PortfolioInterface
{
    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return Portfolio::query()
            ->with('media')
            ->ordered()
            ->paginate($perPage);
    }

    public function getActive(): Collection
    {
        return Portfolio::query()
            ->with('media')
            ->active()
            ->ordered()
            ->get();
    }

    public function getActivePaginated(int $perPage = 12): LengthAwarePaginator
    {
        return Portfolio::query()
            ->with('media')
            ->active()
            ->ordered()
            ->paginate($perPage);
    }

    public function getFeatured(int $limit = 6): Collection
    {
        return Portfolio::query()
            ->with('media')
            ->active()
            ->featured()
            ->ordered()
            ->limit($limit)
            ->get();
    }

    public function find(int $id): ?Portfolio
    {
        return Portfolio::with('media')->find($id);
    }

    public function findBySlug(string $slug): ?Portfolio
    {
        return Portfolio::with('media')->where('slug', $slug)->first();
    }

    public function findActiveBySlug(string $slug): ?Portfolio
    {
        return Portfolio::with('media')
            ->where('slug', $slug)
            ->active()
            ->first();
    }

    public function store(Request $request): Portfolio
    {
        $data = $request->validated();

        // Handle technologies as array
        if (isset($data['technologies']) && is_string($data['technologies'])) {
            $data['technologies'] = array_map('trim', explode(',', $data['technologies']));
        }

        $portfolio = Portfolio::create($data);

        // Handle featured image
        if ($request->hasFile('featured_image')) {
            $portfolio->addMediaFromRequest('featured_image')
                ->toMediaCollection('featured_image');
        }

        // Handle gallery images
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $portfolio->addMedia($file)
                    ->toMediaCollection('gallery');
            }
        }

        // Handle logo
        if ($request->hasFile('logo')) {
            $portfolio->addMediaFromRequest('logo')
                ->toMediaCollection('logo');
        }

        return $portfolio->fresh('media');
    }

    public function update(int $id, Request $request): Portfolio
    {
        $portfolio = $this->find($id);

        if (! $portfolio) {
            abort(404, 'Portfolio not found');
        }

        $data = $request->validated();

        // Handle technologies as array
        if (isset($data['technologies']) && is_string($data['technologies'])) {
            $data['technologies'] = array_map('trim', explode(',', $data['technologies']));
        }

        $portfolio->update($data);

        // Handle featured image
        if ($request->hasFile('featured_image')) {
            $portfolio->clearMediaCollection('featured_image');
            $portfolio->addMediaFromRequest('featured_image')
                ->toMediaCollection('featured_image');
        }

        // Handle gallery images (append new ones)
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $portfolio->addMedia($file)
                    ->toMediaCollection('gallery');
            }
        }

        // Handle logo
        if ($request->hasFile('logo')) {
            $portfolio->clearMediaCollection('logo');
            $portfolio->addMediaFromRequest('logo')
                ->toMediaCollection('logo');
        }

        // Handle gallery deletions
        if ($request->has('delete_gallery')) {
            $deleteIds = $request->input('delete_gallery', []);
            foreach ($deleteIds as $mediaId) {
                $media = $portfolio->getMedia('gallery')->where('id', $mediaId)->first();
                if ($media) {
                    $media->delete();
                }
            }
        }

        return $portfolio->fresh('media');
    }

    public function destroy(int $id): void
    {
        $portfolio = $this->find($id);

        if ($portfolio) {
            $portfolio->clearMediaCollection('featured_image');
            $portfolio->clearMediaCollection('gallery');
            $portfolio->clearMediaCollection('logo');
            $portfolio->delete();
        }
    }

    public function toggleStatus(int $id): Portfolio
    {
        $portfolio = $this->find($id);

        if (! $portfolio) {
            abort(404, 'Portfolio not found');
        }

        $portfolio->is_active = ! $portfolio->is_active;
        $portfolio->save();

        return $portfolio;
    }

    public function toggleFeatured(int $id): Portfolio
    {
        $portfolio = $this->find($id);

        if (! $portfolio) {
            abort(404, 'Portfolio not found');
        }

        $portfolio->is_featured = ! $portfolio->is_featured;
        $portfolio->save();

        return $portfolio;
    }

    public function reorder(array $orderData): void
    {
        DB::transaction(function () use ($orderData) {
            foreach ($orderData as $order => $id) {
                Portfolio::whereKey($id)->update(['order' => $order]);
            }
        });
    }

    public function duplicate(int $id): Portfolio
    {
        $original = $this->find($id);

        if (! $original) {
            abort(404, 'Portfolio not found');
        }

        $duplicate = $original->replicate();
        $duplicate->title = array_map(fn ($t) => $t.' (Copy)', $original->title);
        $duplicate->slug = null; // Will be auto-generated
        $duplicate->is_active = false;
        $duplicate->is_featured = false;
        $duplicate->views_count = 0;
        $duplicate->order = Portfolio::max('order') + 1;
        $duplicate->save();

        // Duplicate media
        try {
            foreach ($original->getMedia('featured_image') as $media) {
                $media->copy($duplicate, 'featured_image');
            }
            foreach ($original->getMedia('gallery') as $media) {
                $media->copy($duplicate, 'gallery');
            }
            foreach ($original->getMedia('logo') as $media) {
                $media->copy($duplicate, 'logo');
            }
        } catch (Throwable $e) {
            // Non-fatal
        }

        return $duplicate->fresh('media');
    }

    public function getByCategory(string $category, int $perPage = 12): LengthAwarePaginator
    {
        return Portfolio::query()
            ->with('media')
            ->active()
            ->byCategory($category)
            ->ordered()
            ->paginate($perPage);
    }

    public function getCategories(): array
    {
        $portfolios = Portfolio::active()->get(['category']);
        $categories = [];

        foreach ($portfolios as $portfolio) {
            $translations = $portfolio->getTranslations('category');
            if (! empty($translations)) {
                foreach (array_keys($translations) as $lang) {
                    $cat = $portfolio->getTranslation('category', $lang);
                    if ($cat && ! in_array($cat, $categories)) {
                        $categories[] = $cat;
                    }
                }
            }
        }

        return array_unique($categories);
    }

    public function search(string $query, int $perPage = 12): LengthAwarePaginator
    {
        return Portfolio::query()
            ->with('media')
            ->active()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('short_description', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%")
                    ->orWhere('client_name', 'like', "%{$query}%")
                    ->orWhere('technologies', 'like', "%{$query}%");
            })
            ->ordered()
            ->paginate($perPage);
    }

    public function getRelated(Portfolio $portfolio, int $limit = 4): Collection
    {
        $category = $portfolio->getTranslation('category', app()->getLocale());

        return Portfolio::query()
            ->with('media')
            ->active()
            ->where('id', '!=', $portfolio->id)
            ->when($category, function ($q) use ($category) {
                $q->where('category', 'like', "%{$category}%");
            })
            ->ordered()
            ->limit($limit)
            ->get();
    }
}
