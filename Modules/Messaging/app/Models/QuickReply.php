<?php

namespace Modules\Messaging\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuickReply extends Model
{
    protected $table = 'messaging_quick_replies';

    protected $fillable = [
        'channel_id',
        'user_id',
        'title',
        'content',
        'shortcut',
        'is_active',
        'is_global',
        'usage_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_global' => 'boolean',
        'usage_count' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    public function scopeForUser($query, User|int $user)
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('is_global', true);
        });
    }

    public function scopeForChannel($query, Channel|int|null $channel)
    {
        if (! $channel) {
            return $query->whereNull('channel_id');
        }

        $channelId = $channel instanceof Channel ? $channel->id : $channel;

        return $query->where(function ($q) use ($channelId) {
            $q->where('channel_id', $channelId)
                ->orWhereNull('channel_id');
        });
    }

    public function scopeMatchingShortcut($query, string $shortcut)
    {
        return $query->where('shortcut', $shortcut);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function incrementUsage(): self
    {
        $this->increment('usage_count');

        return $this;
    }

    public function isOwnedBy(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->user_id === $userId;
    }

    public function isAvailableFor(User|int $user): bool
    {
        if ($this->is_global) {
            return true;
        }

        return $this->isOwnedBy($user);
    }

    /**
     * Apply variable substitutions to the content.
     */
    public function getProcessedContent(array $variables = []): string
    {
        $content = $this->content;

        foreach ($variables as $key => $value) {
            $content = str_replace('{'.$key.'}', $value, $content);
        }

        return $content;
    }
}
