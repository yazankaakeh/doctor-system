<?php

namespace Modules\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPostClap extends Model
{
    protected $fillable = [
        'post_id',
        'session_id',
        'ip_address',
        'user_agent',
        'clap_count',
    ];

    protected $casts = [
        'clap_count' => 'integer',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'post_id');
    }

    public static function getSessionClaps(int $postId, string $sessionId): int
    {
        return self::where('post_id', $postId)
            ->where('session_id', $sessionId)
            ->sum('clap_count');
    }

    public static function canClap(int $postId, string $sessionId): bool
    {
        $currentClaps = self::getSessionClaps($postId, $sessionId);

        return $currentClaps < 50;
    }

    public static function getRemainingClaps(int $postId, string $sessionId): int
    {
        $currentClaps = self::getSessionClaps($postId, $sessionId);

        return max(0, 50 - $currentClaps);
    }
}
