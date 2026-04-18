<?php

namespace Modules\Website\Repository\Blog;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Blog\Enums\PostTypeEnum;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogPost;
use Modules\Blog\Models\BlogPostClap;
use Modules\Blog\Models\BlogPostTags;
use Modules\Core\App\Enums\ActiveEnum;

class BlogFrontRepository implements BlogFrontInterface
{
    public function getPublishedPosts(Request $request, int $perPage = 9): LengthAwarePaginator
    {
        $query = BlogPost::with(['category', 'tags'])
            ->where('is_active', ActiveEnum::ACTIVE)
            ->where('type', PostTypeEnum::PUBLISHED)
            ->latest();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $locale = app()->getLocale();

            $query->where(function ($q) use ($search, $locale) {
                $q
                    ->whereRaw("JSON_EXTRACT(title, '$.\"$locale\"') LIKE ?", ["%$search%"])
                    ->orWhereRaw("JSON_EXTRACT(description, '$.\"$locale\"') LIKE ?", ["%$search%"]);
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->get('category'));
        }

        // Filter by tag
        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('blog_post_tags.id', $request->get('tag'));
            });
        }

        return $query->paginate($perPage);
    }

    public function getPublishedPost(int $id): BlogPost
    {
        return BlogPost::with(['category', 'tags'])
            ->where('is_active', ActiveEnum::ACTIVE)
            ->where('type', PostTypeEnum::PUBLISHED)
            ->findOrFail($id);
    }

    public function getPostsByCategory(int $categoryId, int $perPage = 9): LengthAwarePaginator
    {
        return BlogPost::with(['category', 'tags'])
            ->where('is_active', ActiveEnum::ACTIVE)
            ->where('type', PostTypeEnum::PUBLISHED)
            ->where('category_id', $categoryId)
            ->latest()
            ->paginate($perPage);
    }

    public function getPostsByTag(int $tagId, int $perPage = 9): LengthAwarePaginator
    {
        return BlogPost::with(['category', 'tags'])
            ->where('is_active', ActiveEnum::ACTIVE)
            ->where('type', PostTypeEnum::PUBLISHED)
            ->whereHas('tags', function ($q) use ($tagId) {
                $q->where('blog_post_tags.id', $tagId);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function getActiveCategories(): Collection
    {
        return BlogCategory::query()
            ->where('is_active', ActiveEnum::ACTIVE)
            ->get();
    }

    public function getAllTags(): Collection
    {
        return BlogPostTags::all();
    }

    public function getPopularPosts(int $limit = 5): Collection
    {
        return BlogPost::query()
            ->where('is_active', ActiveEnum::ACTIVE)
            ->where('type', PostTypeEnum::PUBLISHED)
            ->orderBy('clapping', 'desc')
            ->take($limit)
            ->get();
    }

    public function getRelatedPosts(BlogPost $post, int $limit = 3): Collection
    {
        return BlogPost::query()
            ->where('is_active', ActiveEnum::ACTIVE)
            ->where('type', PostTypeEnum::PUBLISHED)
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->take($limit)
            ->get();
    }

    public function getActiveCategory(int $id): BlogCategory
    {
        return BlogCategory::query()
            ->where('is_active', ActiveEnum::ACTIVE)
            ->findOrFail($id);
    }

    public function getTag(int $id): BlogPostTags
    {
        return BlogPostTags::query()->findOrFail($id);
    }

    public function getClapStats(int $postId, string $sessionId): array
    {
        $post = BlogPost::query()->findOrFail($postId);
        $userClaps = BlogPostClap::getSessionClaps($postId, $sessionId);
        $remainingClaps = BlogPostClap::getRemainingClaps($postId, $sessionId);
        $totalClaps = $post->totalClaps();

        return [
            'user_claps' => $userClaps,
            'remaining_claps' => $remainingClaps,
            'total_claps' => $totalClaps,
        ];
    }
}
