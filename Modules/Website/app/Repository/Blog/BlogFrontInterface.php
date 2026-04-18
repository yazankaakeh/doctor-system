<?php

namespace Modules\Website\Repository\Blog;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogPost;
use Modules\Blog\Models\BlogPostTags;

interface BlogFrontInterface
{
    /**
     * Get paginated published blog posts with filters
     */
    public function getPublishedPosts(Request $request, int $perPage = 9): LengthAwarePaginator;

    /**
     * Get a single published post with relations
     */
    public function getPublishedPost(int $id): BlogPost;

    /**
     * Get posts by category
     */
    public function getPostsByCategory(int $categoryId, int $perPage = 9): LengthAwarePaginator;

    /**
     * Get posts by tag
     */
    public function getPostsByTag(int $tagId, int $perPage = 9): LengthAwarePaginator;

    /**
     * Get all active categories
     */
    public function getActiveCategories(): Collection;

    /**
     * Get all tags
     */
    public function getAllTags(): Collection;

    /**
     * Get popular posts
     */
    public function getPopularPosts(int $limit = 5): Collection;

    /**
     * Get related posts for a specific post
     */
    public function getRelatedPosts(BlogPost $post, int $limit = 3): Collection;

    /**
     * Get active category by ID
     */
    public function getActiveCategory(int $id): BlogCategory;

    /**
     * Get tag by ID
     */
    public function getTag(int $id): BlogPostTags;

    /**
     * Get user clap stats for a post
     */
    public function getClapStats(int $postId, string $sessionId): array;
}
