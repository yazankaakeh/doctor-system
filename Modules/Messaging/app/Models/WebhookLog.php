<?php

/**
 * -----------------------------------------------------------------------------
 * WebhookLog Model
 * -----------------------------------------------------------------------------
 *
 * Persistent record of every webhook payload received from an external
 * channel provider (WhatsApp, Telegram, …). Stored verbatim so that:
 *
 *   - Failed deliveries can be reprocessed after a fix/deploy.
 *   - Developers can inspect the raw payload when diagnosing issues.
 *   - Duplicate webhooks can be detected and de-duplicated.
 *
 * The `processed` flag and optional `error` string capture the processing
 * outcome; `processed_at` stamps when handling finished.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Messaging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    protected $table = 'messaging_webhook_logs';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'channel_id',   // FK → messaging_channels.id
        'event_type',   // Provider-reported event name
        'payload',      // Full JSON body
        'headers',      // Select request headers (for debugging)
        'processed',    // Has our handler finished running?
        'error',        // Error message (null = success)
        'processed_at', // When handling concluded
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

    /** Channel the webhook belongs to. */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /** Logs that have been fully handled. */
    public function scopeProcessed($query)
    {
        return $query->where('processed', true);
    }

    /** Logs still waiting to be handled (e.g. queue backlog). */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    /** Logs whose handler raised an error. */
    public function scopeFailed($query)
    {
        return $query->whereNotNull('error');
    }

    /** Restrict to a given event type. */
    public function scopeOfType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /** Restrict to a specific channel. */
    public function scopeForChannel($query, Channel|int $channel)
    {
        $channelId = $channel instanceof Channel ? $channel->id : $channel;

        return $query->where('channel_id', $channelId);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /** Flip to processed + stamp processed_at (success path). */
    public function markAsProcessed(): self
    {
        $this->update([
            'processed' => true,
            'processed_at' => now(),
        ]);

        return $this;
    }

    /** Flip to processed but record the failure reason. */
    public function markAsFailed(string $error): self
    {
        $this->update([
            'processed' => true,
            'processed_at' => now(),
            'error' => $error,
        ]);

        return $this;
    }

    /** Convenience check for failure state. */
    public function hasFailed(): bool
    {
        return ! empty($this->error);
    }

    /** Safely pull a dot-notation key out of the payload JSON. */
    public function getPayloadValue(string $key, $default = null)
    {
        return data_get($this->payload, $key, $default);
    }
}
