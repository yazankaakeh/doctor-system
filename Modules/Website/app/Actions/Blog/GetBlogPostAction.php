<?php

namespace Modules\Website\Actions\Blog;

use Modules\Website\Repository\Blog\BlogFrontInterface;

class GetBlogPostAction
{
    public function __construct(
        private readonly BlogFrontInterface $blogRepository
    ) {}

    public function handle(int $id, string $sessionId): array
    {
        $post = $this->blogRepository->getPublishedPost($id);
        $clapStats = $this->blogRepository->getClapStats($id, $sessionId);
        $relatedPosts = $this->blogRepository->getRelatedPosts($post, 3);
        $popularPosts = $this->blogRepository->getPopularPosts(5);

        return [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'popularPosts' => $popularPosts,
            'userClaps' => $clapStats['user_claps'],
            'remainingClaps' => $clapStats['remaining_claps'],
            'totalClaps' => $clapStats['total_claps'],
        ];
    }
}
