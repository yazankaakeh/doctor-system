<?php

namespace Modules\Messaging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    protected $table = 'messaging_webhook_logs';

    protected $fillable = [
        'channel_id',
        'event_type',
        'payload',
        'headers',
        'processed',
        'error',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeProcessed($query)
    {
        return $query->where('processed', true);
    }

    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    public function scopeFailed($query)
    {
        return $query->whereNotNull('error');
    }

    public function scopeOfType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeForChannel($query, Channel|int $channel)
    {
        $channelId = $channel instanceof Channel ? $channel->id : $channel;

        return $query->where('channel_id', $channelId);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function markAsProcessed(): self
    {
        $this->update([
            'processed' => true,
            'processed_at' => now(),
        ]);

        return $this;
    }

    public function markAsFailed(string $error): self
    {
        $this->update([
            'processed' => true,
            'processed_at' => now(),
            'error' => $error,
        ]);

        return $this;
    }

    public function hasFailed(): bool
    {
        return ! empty($this->error);
    }

    public function getPayloadValue(string $key, $default = null)
    {
        return data_get($this->payload, $key, $default);
    }
}
