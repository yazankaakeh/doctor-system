<?php

/**
 * -----------------------------------------------------------------------------
 * ConversationNote Model
 * -----------------------------------------------------------------------------
 *
 * An internal, agent-only note attached to a Conversation. Notes are never
 * delivered to the customer — they're meant to help agents collaborate
 * ("called back, left voicemail", "escalate to billing", etc.).
 * -----------------------------------------------------------------------------
 */

namespace Modules\Messaging\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationNote extends Model
{
    protected $table = 'messaging_conversation_notes';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id', // FK → messaging_conversations.id
        'user_id',         // FK → users.id (note author)
        'content',         // Free-text body
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /** Parent conversation. */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    /** User who authored the note. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /** Newest note first (used by the notes panel). */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /** Restrict to notes written by a specific user. */
    public function scopeByUser($query, User|int $user)
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('user_id', $userId);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /** True if the given user authored this note (used to gate edit/delete). */
    public function isOwnedBy(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->user_id === $userId;
    }
}
