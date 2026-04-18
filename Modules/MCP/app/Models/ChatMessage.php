<?php

namespace Modules\MCP\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_id',
        'role',
        'message',
        'context',
        'is_from_bot',
        'model_used',
        'tokens_used',
        'metadata',
    ];

    protected $casts = [
        'context' => 'array',
        'is_from_bot' => 'boolean',
        'tokens_used' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the conversation this message belongs to
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    /**
     * Get the sender (User, Bot, Guest, etc.)
     */
    public function sender(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get feedback for this message
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(ChatFeedback::class, 'message_id');
    }

    /**
     * Scope for bot messages
     */
    public function scopeFromBot($query)
    {
        return $query->where('is_from_bot', true);
    }

    /**
     * Scope for user messages
     */
    public function scopeFromUser($query)
    {
        return $query->where('is_from_bot', false);
    }

    /**
     * Scope by role
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Check if message is from bot
     */
    public function isFromBot(): bool
    {
        return $this->is_from_bot === true;
    }

    /**
     * Check if message is from user
     */
    public function isFromUser(): bool
    {
        return $this->is_from_bot === false;
    }
}
