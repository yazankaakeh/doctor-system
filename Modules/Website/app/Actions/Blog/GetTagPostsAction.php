<?php

namespace Modules\Website\Actions\Blog;

use Modules\Website\Repository\Blog\BlogFrontInterface;

class GetTagPostsAction
{
    public function __construct(
        private readonly BlogFrontInterface $blogRepository
    ) {}

    public function handle(int $tagId): array
    {
        $tag = $this->blogRepository->getTag($tagId);
        $posts = $this->blogRepository->getPostsByTag($tagId, 9);
        $categories = $this->blogRepository->getActiveCategories();
        $tags = $this->blogRepository->getAllTags();
        $popularPosts = $this->blogRepository->getPopularPosts(5);

        return [
            'tag' => $tag,
            'posts' => $posts,
            'categories' => $categories,
            'tags' => $tags,
            'popularPosts' => $popularPosts,
        ];
    }
}
