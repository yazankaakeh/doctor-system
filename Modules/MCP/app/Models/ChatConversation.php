<?php

namespace Modules\MCP\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatConversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversationable_type',
        'conversationable_id',
        'session_id',
        'ip_address',
        'user_agent',
        'status',
        'metadata',
        'last_message_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_message_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the owning conversationable model (User, Guest, Doctor, etc.)
     */
    public function conversationable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all messages for this conversation
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get feedback for this conversation
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(ChatFeedback::class, 'conversation_id');
    }

    /**
     * Scope for active conversations
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for conversations by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('conversationable_type', $type);
    }

    /**
     * Get the latest message
     */
    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')
            ->latestOfMany();
    }

    /**
     * Mark conversation as closed
     */
    public function close(): bool
    {
        return $this->update(['status' => 'closed']);
    }

    /**
     * Mark conversation as archived
     */
    public function archive(): bool
    {
        return $this->update(['status' => 'archived']);
    }
}
