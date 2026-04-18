<?php

namespace Modules\Website\Actions\Blog;

use Illuminate\Http\Request;
use Modules\Website\Repository\Blog\BlogFrontInterface;

class GetBlogPostsAction
{
    public function __construct(
        private readonly BlogFrontInterface $blogRepository
    ) {}

    public function handle(Request $request): array
    {
        $posts = $this->blogRepository->getPublishedPosts($request, 9);
        $categories = $this->blogRepository->getActiveCategories();
        $tags = $this->blogRepository->getAllTags();
        $popularPosts = $this->blogRepository->getPopularPosts(5);

        return [
            'posts' => $posts,
            'categories' => $categories,
            'tags' => $tags,
            'popularPosts' => $popularPosts,
        ];
    }
}
