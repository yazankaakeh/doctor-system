<?php

/**
 * -----------------------------------------------------------------------------
 * QuickReply Model
 * -----------------------------------------------------------------------------
 *
 * Canned responses that agents can insert into the composer with a shortcut
 * (e.g. `/hello`, `/directions`). Quick replies can be:
 *   - Personal to a user (`user_id`) or global (`is_global = true`).
 *   - Scoped to a specific channel (`channel_id`) or available everywhere.
 *
 * The `usage_count` column is incremented every time the reply is inserted
 * so the UI can surface the most-used templates first.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Messaging\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuickReply extends Model
{
    protected $table = 'messaging_quick_replies';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'channel_id',   // Optional — restrict to a channel
        'user_id',      // Owner (null for globals)
        'title',        // Label shown in the composer menu
        'content',      // Body with optional {placeholders}
        'shortcut',     // Trigger text (e.g. "/hi")
        'is_active',    // Soft toggle
        'is_global',    // Visible to all users regardless of owner
        'usage_count',  // Bumps each time the reply is inserted
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_global'   => 'boolean',
        'usage_count' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /** Channel this reply is restricted to (nullable). */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }

    /** Owner user (null when the reply is global). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /** Only enabled replies. */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Only globally-shared replies. */
    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    /** Replies accessible to the given user (owned OR global). */
    public function scopeForUser($query, User|int $user)
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('is_global', true);
        });
    }

    /**
     * Replies relevant to a given channel (explicit match OR global-channel
     * ones where channel_id is null).
     */
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

    /** Find a reply by its typed shortcut (exact match). */
    public function scopeMatchingShortcut($query, string $shortcut)
    {
        return $query->where('shortcut', $shortcut);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /** Atomically bump usage_count by 1 — called after inserting the reply. */
    public function incrementUsage(): self
    {
        $this->increment('usage_count');

        return $this;
    }

    /** True when the given user owns this quick reply. */
    public function isOwnedBy(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->user_id === $userId;
    }

    /** True when the given user is allowed to see/use this reply. */
    public function isAvailableFor(User|int $user): bool
    {
        if ($this->is_global) {
            return true;
        }

        return $this->isOwnedBy($user);
    }

    /**
     * Replace `{placeholder}` tokens in the content with values from
     * $variables and return the processed string. Used when an agent picks
     * a reply and the composer fills in contextual values like {name}.
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
