<?php

namespace Modules\Website\Actions\Blog;

use Modules\Website\Repository\Blog\BlogFrontInterface;

class GetCategoryPostsAction
{
    public function __construct(
        private readonly BlogFrontInterface $blogRepository
    ) {}

    public function handle(int $categoryId): array
    {
        $category = $this->blogRepository->getActiveCategory($categoryId);
        $posts = $this->blogRepository->getPostsByCategory($categoryId, 9);
        $categories = $this->blogRepository->getActiveCategories();
        $tags = $this->blogRepository->getAllTags();
        $popularPosts = $this->blogRepository->getPopularPosts(5);

        return [
            'category' => $category,
            'posts' => $posts,
            'categories' => $categories,
            'tags' => $tags,
            'popularPosts' => $popularPosts,
        ];
    }
}
