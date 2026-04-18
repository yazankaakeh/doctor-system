<?php

namespace Modules\CMS\Repository\Page;

use App\Enum\Pagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\CMS\Models\Page;
use Modules\Seo\Models\SeoMeta;

class PageRepository implements PageInterface
{
    public function index(): LengthAwarePaginator
    {
        return Page::query()
            ->with(['parent'])
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->paginate(Pagination::PAG->value);
    }

    public function store(Request $request): Page
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($request, $validated) {
            $page = new Page;
            $page->title = $validated['title'];
            $page->slug = $validated['slug'];
            $page->content = $validated['content'] ?? [];
            $page->excerpt = $validated['excerpt'] ?? [];
            $page->status = $validated['status'];
            $page->template = $validated['template'];
            $page->parent_id = $validated['parent_id'] ?? null;
            $page->order = $validated['order'] ?? 0;
            $page->meta_data = $validated['meta_data'] ?? [];
            $page->published_at = $validated['published_at'] ?? null;
            $page->use_panel_builder = $request->boolean('use_panel_builder');
            $page->save();

            if ($request->hasFile('featured_image')) {
                $page->addMediaFromRequest('featured_image')->toMediaCollection('featured_image');
            }

            $this->saveSeo($page, $validated);

            return $page;
        });
    }

    private function saveSeo(Page $page, array $validated): void
    {
        $metaTitle = $validated['meta_title'] ?? null;
        $metaDescription = $validated['meta_description'] ?? null;

        if ($metaTitle || $metaDescription) {
            $seo = $page->seo ?: new SeoMeta;
            if ($metaTitle) {
                $seo->title = $metaTitle;
            }
            if ($metaDescription) {
                $seo->meta_description = $metaDescription;
            }
            $page->seo()->save($seo);
        }
    }

    public function update(int $id, Request $request): Page
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($id, $request, $validated) {
            $page = $this->find($id);
            $page->title = $validated['title'];
            $page->slug = $validated['slug'];
            $page->content = $validated['content'] ?? [];
            $page->excerpt = $validated['excerpt'] ?? [];
            $page->status = $validated['status'];
            $page->template = $validated['template'];
            $page->parent_id = $validated['parent_id'] ?? null;
            $page->order = $validated['order'] ?? 0;
            $page->meta_data = $validated['meta_data'] ?? [];
            $page->published_at = $validated['published_at'] ?? null;
            $page->use_panel_builder = $request->boolean('use_panel_builder');
            $page->save();

            // Handle featured image
            if ($request->boolean('remove_featured_image')) {
                $page->clearMediaCollection('featured_image');
            } elseif ($request->hasFile('featured_image')) {
                $page->clearMediaCollection('featured_image');
                $page->addMediaFromRequest('featured_image')->toMediaCollection('featured_image');
            }

            $this->saveSeo($page, $validated);

            return $page;
        });
    }

    public function find(int $id): Page
    {
        return Page::query()->with(['parent', 'children'])->findOrFail($id);
    }

    public function destroy(int $id): void
    {
        $page = $this->find($id);
        $page->delete();
    }

    public function getParentOptions(): array
    {
        return Page::query()
            ->select(['id', 'title'])
            ->whereNull('parent_id')
            ->get()
            ->mapWithKeys(function (Page $page) {
                $title = $page->getTranslation('title', app()->getLocale());

                return [$page->id => is_string($title) ? $title : (json_encode($title) ?: 'Page '.$page->id)];
            })->toArray();
    }

    public function findBySlug(string $slug): ?Page
    {
        return Page::query()
            ->with(['parent', 'children'])
            ->bySlug($slug)
            ->first();
    }

    public function getHomePage(): ?Page
    {
        return $this->findBySlug('home');
    }
}
