<?php

/**
 * -----------------------------------------------------------------------------
 * Conversation Model
 * -----------------------------------------------------------------------------
 *
 * Central entity in the Messaging module. A Conversation represents a thread
 * of messages between two parties: a "participant" (customer / patient /
 * lead identified by phone, email, or user-id) and optionally an assigned
 * internal user (agent / doctor).
 *
 * Each conversation:
 *   - Belongs to exactly one Channel (the pipe through which messages flow —
 *     Web-chat, WhatsApp, Telegram, Email, etc.).
 *   - Has many Messages and many ConversationNotes (internal notes).
 *   - May be polymorphically linked to another domain model via
 *     conversable_type/id (e.g. a Booking, a Lead) so the thread can be
 *     embedded in that entity's UI.
 *
 * Key state fields:
 *   - status        : ConversationStatusEnum (OPEN / PENDING / CLOSED)
 *   - priority      : ConversationPriorityEnum (low / normal / high / urgent)
 *   - unread_count  : in-app unread badge counter
 *   - last_message_at : used for inbox sorting
 *
 * The helpers at the bottom resolve "the other party's display name" from
 * the viewer's perspective, which is how the inbox renders thread titles.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Messaging\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\ConversationPriorityEnum;
use Modules\Messaging\Enums\ConversationStatusEnum;

class Conversation extends Model
{
    // Soft-deleting keeps message history auditable even after "deletion".
    use SoftDeletes;

    /** Custom table name so the migration doesn't collide with other modules. */
    protected $table = 'messaging_conversations';

    /**
     * Mass-assignable columns. See the migration for full semantics.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'channel_id',
        'conversable_type',
        'conversable_id',
        'assigned_user_id',
        'participant_identifier',
        'participant_name',
        'status',
        'priority',
        'last_message_at',
        'unread_count',
        'external_conversation_id',
        'metadata',
    ];

    protected $casts = [
        'status' => ConversationStatusEnum::class,
        'priority' => ConversationPriorityEnum::class,
        'last_message_at' => 'datetime',
        'unread_count' => 'integer',
        'metadata' => 'array',
    ];

    // =========================================================================
    // BOOT
    // =========================================================================

    /**
     * Auto-assign a UUID on create. Useful for public references, webhook
     * correlation, and the chat widget URL so we never leak the numeric ID.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($conversation) {
            if (empty($conversation->uuid)) {
                $conversation->uuid = (string) Str::uuid();
            }
        });
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /** The channel through which this thread is delivered (WhatsApp, web, …). */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }

    /**
     * Polymorphic owner: any model (Booking, Lead, Order…) can attach a
     * conversation to itself through conversable_type/id.
     */
    public function conversable(): MorphTo
    {
        return $this->morphTo();
    }

    /** Internal user who owns the thread (agent / doctor). */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /** All messages in chronological order (ordering is applied via scopes). */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    /** Internal-only notes visible to staff but not to the participant. */
    public function notes(): HasMany
    {
        return $this->hasMany(ConversationNote::class, 'conversation_id');
    }

    // =========================================================================
    // SCOPES — reusable query constraints for the inbox / dashboards.
    // =========================================================================

    /** Only threads currently in OPEN state. */
    public function scopeOpen($query)
    {
        return $query->where('status', ConversationStatusEnum::OPEN);
    }

    /** Threads that are OPEN or PENDING — excludes CLOSED. */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            ConversationStatusEnum::OPEN,
            ConversationStatusEnum::PENDING,
        ]);
    }

    /** Only CLOSED threads. */
    public function scopeClosed($query)
    {
        return $query->where('status', ConversationStatusEnum::CLOSED);
    }

    /** Threads assigned to a given user (either User model or id). */
    public function scopeAssignedTo($query, User|int $user)
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('assigned_user_id', $userId);
    }

    /** Threads that haven't been claimed by any agent yet. */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_user_id');
    }

    /**
     * Filter by channel. Accepts either a Channel model or a ChannelTypeEnum
     * — using the enum form restricts to channels of that type (could be
     * multiple WhatsApp numbers for example).
     */
    public function scopeForChannel($query, ChannelTypeEnum|Channel $channel)
    {
        if ($channel instanceof Channel) {
            return $query->where('channel_id', $channel->id);
        }

        return $query->whereHas('channel', fn ($q) => $q->where('type', $channel));
    }

    /** Find all threads for a particular participant (phone/email/user:id). */
    public function scopeForParticipant($query, string $identifier)
    {
        return $query->where('participant_identifier', $identifier);
    }

    /** Threads with at least one unread message. */
    public function scopeWithUnread($query)
    {
        return $query->where('unread_count', '>', 0);
    }

    /**
     * Order threads with the "most urgent first" convention used by the
     * inbox: urgent → high → normal → low.
     */
    public function scopeByPriority($query)
    {
        return $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')");
    }

    // =========================================================================
    // HELPERS — small fluent actions that consuming code uses instead of
    // manipulating columns directly.
    // =========================================================================

    /** Shortcut to the channel's enum type. */
    public function getChannelType(): ChannelTypeEnum
    {
        return $this->channel->type;
    }

    /** True when the status is OPEN or PENDING. */
    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    /** True when the current status permits sending a new message. */
    public function canSendMessage(): bool
    {
        return $this->status->canSendMessage();
    }

    /**
     * Reset the unread counter and stamp read_at on any unread messages.
     * Works for both inbound and outbound messages so the "read" badge
     * clears on both sides of a user-to-user thread.
     */
    public function markAsRead(): self
    {
        $this->update(['unread_count' => 0]);

        $this->messages()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this;
    }

    /** Bump the unread counter (usually by 1 per new inbound message). */
    public function incrementUnread(int $count = 1): self
    {
        $this->increment('unread_count', $count);

        return $this;
    }

    /** Mark that new activity happened, so the inbox can sort this thread up. */
    public function updateLastMessageAt(): self
    {
        $this->update(['last_message_at' => now()]);

        return $this;
    }

    /** Assign the thread to a specific internal user (agent). */
    public function assignTo(User|int $user): self
    {
        $userId = $user instanceof User ? $user->id : $user;
        $this->update(['assigned_user_id' => $userId]);

        return $this;
    }

    /** Remove the current assignee so the thread becomes unclaimed again. */
    public function unassign(): self
    {
        $this->update(['assigned_user_id' => null]);

        return $this;
    }

    /** Transition the thread to CLOSED state. */
    public function close(): self
    {
        $this->update(['status' => ConversationStatusEnum::CLOSED]);

        return $this;
    }

    /** Re-open a closed thread (e.g. customer replied again). */
    public function reopen(): self
    {
        $this->update(['status' => ConversationStatusEnum::OPEN]);

        return $this;
    }

    /** Last message in the thread (nullable for empty conversations). */
    public function getLastMessage(): ?Message
    {
        return $this->messages()->latest()->first();
    }

    /** Accessor alias so Blade can use $conversation->last_message. */
    public function getLastMessageAttribute(): ?Message
    {
        return $this->getLastMessage();
    }

    /**
     * Default display name — resolved against the currently authenticated user.
     * Preferred for quick Blade output; pass a user explicitly with
     * getDisplayNameForUser() when rendering on behalf of someone else.
     */
    public function getDisplayName(): string
    {
        return $this->getDisplayNameForUser(auth()->user());
    }

    /**
     * Resolve the display name from the perspective of the given user.
     *
     * A Conversation has two sides:
     *   - participant     → the customer (identified by phone/email/user id)
     *   - assigned user   → the internal agent (optional)
     *
     * When rendering the thread title we always want to show the "other"
     * party's name — this method hides that decision tree from callers.
     */
    public function getDisplayNameForUser(?User $currentUser): string
    {
        // Unauthenticated → best effort: fall back to participant label.
        if (! $currentUser) {
            return $this->participant_name ?? $this->participant_identifier;
        }

        // Is the current user the "participant" side of this thread?
        $isCurrentUserParticipant = $this->isUserTheParticipant($currentUser);

        if ($isCurrentUserParticipant) {
            // From the participant's perspective, we show the AGENT's name.
            // Prefer the explicitly-assigned user; otherwise fall back to
            // whoever actually sent messages in the thread.
            if ($this->assigned_user_id && $this->assigned_user_id !== $currentUser->id) {
                return $this->assignedUser?->name ?? 'Agent';
            }

            // Look up the other person from the message history.
            $otherUser = $this->getOtherUserFromMessages($currentUser);
            if ($otherUser) {
                return $otherUser->name;
            }

            return 'Agent';
        }

        // From the agent's perspective, we show the PARTICIPANT's name.
        if ($this->participant_name) {
            return $this->participant_name;
        }

        // No explicit name — try to resolve a User row by the identifier.
        $participantUser = $this->findUserByIdentifier($this->participant_identifier);
        if ($participantUser) {
            return $participantUser->name;
        }

        // As a last resort defer to the polymorphic owner (e.g. a Booking
        // could implement getMessagingDisplayName() to surface its patient).
        if ($this->conversable && method_exists($this->conversable, 'getMessagingDisplayName')) {
            return $this->conversable->getMessagingDisplayName();
        }

        // Nothing else worked — show the raw identifier.
        return $this->participant_identifier;
    }

    /**
     * Is the given user the "participant" (customer side) of this thread?
     *
     * Checks three possible identifier shapes:
     *   1. full mobile number
     *   2. email address
     *   3. synthetic "user:<id>" identifier used for user-to-user chat
     */
    public function isUserTheParticipant(User $user): bool
    {
        if ($user->full_mobile && $this->participant_identifier === $user->full_mobile) {
            return true;
        }

        if ($user->email && $this->participant_identifier === $user->email) {
            return true;
        }

        if (str_starts_with($this->participant_identifier, 'user:')) {
            $participantUserId = (int) str_replace('user:', '', $this->participant_identifier);

            return $participantUserId === $user->id;
        }

        return false;
    }

    /**
     * Find whichever user – other than $currentUser – has sent a message in
     * this conversation. Used to disambiguate names in internal user-to-user
     * threads where neither side is an "agent" per se.
     */
    protected function getOtherUserFromMessages(User $currentUser): ?User
    {
        return $this->messages()
            ->whereNotNull('sender_id')
            ->where('sender_id', '!=', $currentUser->id)
            ->with('sender')
            ->first()
            ?->sender;
    }

    /**
     * Resolve a User by whatever shape of participant_identifier we have:
     * phone number, email, or synthetic "user:<id>" ref. Returns null if
     * nothing matches.
     */
    protected function findUserByIdentifier(?string $identifier): ?User
    {
        if (! $identifier) {
            return null;
        }

        // Try phone.
        $user = User::where('full_mobile', $identifier)->first();
        if ($user) {
            return $user;
        }

        // Try email.
        $user = User::where('email', $identifier)->first();
        if ($user) {
            return $user;
        }

        // Try the "user:<id>" synthetic form.
        if (str_starts_with($identifier, 'user:')) {
            $userId = (int) str_replace('user:', '', $identifier);

            return User::find($userId);
        }

        return null;
    }
}
