<?php

namespace Modules\Website\Actions\Blog;

use Illuminate\Http\Request;
use Modules\Blog\Models\BlogPost;
use Modules\Blog\Models\BlogPostClap;

class ClapBlogPostAction
{
    public function handle(Request $request, int $id): array
    {
        $post = BlogPost::query()->findOrFail($id);
        $sessionId = session()->getId();

        // Check if user can still clap
        if (! BlogPostClap::canClap($id, $sessionId)) {
            return [
                'success' => false,
                'message' => 'You have reached the maximum number of claps (50) for this post.',
                'total_claps' => $post->totalClaps(),
                'user_claps' => 50,
                'remaining_claps' => 0,
            ];
        }

        // Add clap
        BlogPostClap::query()->create([
            'post_id' => $id,
            'session_id' => $sessionId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'clap_count' => 1,
        ]);

        // Update post total clapping count
        $post->increment('clapping');

        $userClaps = BlogPostClap::getSessionClaps($id, $sessionId);
        $remainingClaps = BlogPostClap::getRemainingClaps($id, $sessionId);

        return [
            'success' => true,
            'message' => 'Thanks for your support!',
            'total_claps' => $post->totalClaps(),
            'user_claps' => $userClaps,
            'remaining_claps' => $remainingClaps,
        ];
    }
}
