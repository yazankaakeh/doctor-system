<?php

namespace Modules\MCP\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ChatFeedback extends Model
{
    use HasFactory;

    protected $table = 'chat_feedback';

    protected $fillable = [
        'message_id',
        'conversation_id',
        'reviewer_type',
        'reviewer_id',
        'rating',
        'score',
        'comment',
        'metadata',
    ];

    protected $casts = [
        'score' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the message this feedback belongs to
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    /**
     * Get the conversation this feedback belongs to
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    /**
     * Get the reviewer
     */
    public function reviewer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for helpful feedback
     */
    public function scopeHelpful($query)
    {
        return $query->where('rating', 'helpful');
    }

    /**
     * Scope for not helpful feedback
     */
    public function scopeNotHelpful($query)
    {
        return $query->where('rating', 'not_helpful');
    }

    /**
     * Scope by score
     */
    public function scopeByScore($query, int $score)
    {
        return $query->where('score', $score);
    }
}
