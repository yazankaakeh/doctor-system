<?php

namespace Modules\Blog\Repository\Category;

use App\Enum\Pagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Blog\Models\BlogCategory;
use Modules\Seo\Models\SeoMeta;

class CategoryRepository implements CategoryInterface
{
    public function index(): LengthAwarePaginator
    {
        return BlogCategory::query()->paginate(Pagination::PAG->value);
    }

    public function store(Request $request): BlogCategory
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($request, $validated) {
            $category = new BlogCategory;
            $category->title = $validated['title'];
            $category->description = $validated['description'] ?? [];
            $category->is_active = $request->boolean('is_active') ? 1 : 0;
            $category->save();

            if ($request->hasFile('image')) {
                $category->addMediaFromRequest('image')->toMediaCollection('img');
            }

            $this->saveSeo($category, $validated);

            return $category;
        });
    }

    public function find(int $id): BlogCategory
    {
        return BlogCategory::query()->findOrFail($id);
    }

    public function update(int $id, Request $request): BlogCategory
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($id, $request, $validated) {
            $category = $this->find($id);
            $category->title = $validated['title'];
            $category->description = $validated['description'] ?? [];
            $category->is_active = $request->boolean('is_active') ? 1 : 0;
            $category->save();

            if ($request->hasFile('image')) {
                $category->addMediaFromRequest('image')->toMediaCollection('img');
            }

            $this->saveSeo($category, $validated);

            return $category;
        });
    }

    public function destroy(int $id): void
    {
        $category = $this->find($id);
        $category->delete();
    }

    private function saveSeo(BlogCategory $category, array $validated): void
    {
        $metaTitle = $validated['meta_title'] ?? null;
        $metaDescription = $validated['meta_description'] ?? null;
        if ($metaTitle || $metaDescription) {
            $seo = $category->seo ?: new SeoMeta;
            if ($metaTitle) {
                $seo->title = $metaTitle;
            }
            if ($metaDescription) {
                $seo->meta_description = $metaDescription;
            }
            $category->seo()->save($seo);
        }
    }
}
