<?php

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
    use SoftDeletes;

    protected $table = 'messaging_conversations';

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

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }

    public function conversable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ConversationNote::class, 'conversation_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeOpen($query)
    {
        return $query->where('status', ConversationStatusEnum::OPEN);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            ConversationStatusEnum::OPEN,
            ConversationStatusEnum::PENDING,
        ]);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', ConversationStatusEnum::CLOSED);
    }

    public function scopeAssignedTo($query, User|int $user)
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('assigned_user_id', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_user_id');
    }

    public function scopeForChannel($query, ChannelTypeEnum|Channel $channel)
    {
        if ($channel instanceof Channel) {
            return $query->where('channel_id', $channel->id);
        }

        return $query->whereHas('channel', fn ($q) => $q->where('type', $channel));
    }

    public function scopeForParticipant($query, string $identifier)
    {
        return $query->where('participant_identifier', $identifier);
    }

    public function scopeWithUnread($query)
    {
        return $query->where('unread_count', '>', 0);
    }

    public function scopeByPriority($query)
    {
        return $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')");
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function getChannelType(): ChannelTypeEnum
    {
        return $this->channel->type;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function canSendMessage(): bool
    {
        return $this->status->canSendMessage();
    }

    public function markAsRead(): self
    {
        $this->update(['unread_count' => 0]);

        // Mark all unread messages as read (both inbound and outbound for user-to-user chat)
        $this->messages()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this;
    }

    public function incrementUnread(int $count = 1): self
    {
        $this->increment('unread_count', $count);

        return $this;
    }

    public function updateLastMessageAt(): self
    {
        $this->update(['last_message_at' => now()]);

        return $this;
    }

    public function assignTo(User|int $user): self
    {
        $userId = $user instanceof User ? $user->id : $user;
        $this->update(['assigned_user_id' => $userId]);

        return $this;
    }

    public function unassign(): self
    {
        $this->update(['assigned_user_id' => null]);

        return $this;
    }

    public function close(): self
    {
        $this->update(['status' => ConversationStatusEnum::CLOSED]);

        return $this;
    }

    public function reopen(): self
    {
        $this->update(['status' => ConversationStatusEnum::OPEN]);

        return $this;
    }

    public function getLastMessage(): ?Message
    {
        return $this->messages()->latest()->first();
    }

    public function getLastMessageAttribute(): ?Message
    {
        return $this->getLastMessage();
    }

    public function getDisplayName(): string
    {
        // For user-to-user chat, show the "other" person's name
        return $this->getDisplayNameForUser(auth()->user());
    }

    /**
     * Get display name from the perspective of a specific user.
     * Shows the "other" participant's name.
     */
    public function getDisplayNameForUser(?User $currentUser): string
    {
        if (! $currentUser) {
            return $this->participant_name ?? $this->participant_identifier;
        }

        // Check if current user is the participant (by identifier)
        $isCurrentUserParticipant = $this->isUserTheParticipant($currentUser);

        if ($isCurrentUserParticipant) {
            // Current user IS the participant, so show the other person's name
            // The "other" person could be the assigned user or whoever sent messages
            if ($this->assigned_user_id && $this->assigned_user_id !== $currentUser->id) {
                return $this->assignedUser?->name ?? 'Agent';
            }

            // Try to find the other user from messages
            $otherUser = $this->getOtherUserFromMessages($currentUser);
            if ($otherUser) {
                return $otherUser->name;
            }

            return 'Agent';
        }

        // Current user is NOT the participant (they are the agent/sender)
        // So show the participant's name
        if ($this->participant_name) {
            return $this->participant_name;
        }

        // Try to find participant user by identifier
        $participantUser = $this->findUserByIdentifier($this->participant_identifier);
        if ($participantUser) {
            return $participantUser->name;
        }

        if ($this->conversable && method_exists($this->conversable, 'getMessagingDisplayName')) {
            return $this->conversable->getMessagingDisplayName();
        }

        return $this->participant_identifier;
    }

    /**
     * Check if a user is the participant of this conversation.
     */
    public function isUserTheParticipant(User $user): bool
    {
        // Check by phone number
        if ($user->full_mobile && $this->participant_identifier === $user->full_mobile) {
            return true;
        }

        // Check by email
        if ($user->email && $this->participant_identifier === $user->email) {
            return true;
        }

        // Check by user ID in identifier (format: "user:123")
        if (str_starts_with($this->participant_identifier, 'user:')) {
            $participantUserId = (int) str_replace('user:', '', $this->participant_identifier);

            return $participantUserId === $user->id;
        }

        return false;
    }

    /**
     * Find the other user from messages in this conversation.
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
     * Find a user by their identifier (phone or email).
     */
    protected function findUserByIdentifier(?string $identifier): ?User
    {
        if (! $identifier) {
            return null;
        }

        // Check by phone
        $user = User::where('full_mobile', $identifier)->first();
        if ($user) {
            return $user;
        }

        // Check by email
        $user = User::where('email', $identifier)->first();
        if ($user) {
            return $user;
        }

        // Check by user ID format
        if (str_starts_with($identifier, 'user:')) {
            $userId = (int) str_replace('user:', '', $identifier);

            return User::find($userId);
        }

        return null;
    }
}
