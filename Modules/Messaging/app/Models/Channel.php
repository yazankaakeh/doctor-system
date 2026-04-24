<?php

/**
 * -----------------------------------------------------------------------------
 * Channel Model
 * -----------------------------------------------------------------------------
 *
 * Represents a single messaging channel (e.g. one WhatsApp business number,
 * one Telegram bot, the built-in web-chat widget, an email inbox). Channels
 * are the pipes through which Conversations are delivered.
 *
 * Key fields:
 *   - `type`          → ChannelTypeEnum (whatsapp / telegram / webchat / …).
 *   - `config`        → provider-specific JSON blob (tokens, phone IDs, …).
 *   - `metadata`      → free-form JSON for display metadata (icon, label, …).
 *   - `is_admin_only` → hide from public chat surfaces (internal agents only).
 *
 * Relationships: has many Conversations, Templates, QuickReplies and
 * WebhookLogs — basically everything in the module is partitioned by channel.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Messaging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Messaging\Enums\ChannelTypeEnum;

class Channel extends Model
{
    protected $table = 'messaging_channels';

    /**
     * Mass-assignable columns for the messaging_channels table.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',          // Friendly display name (e.g. "Clinic WhatsApp")
        'type',          // ChannelTypeEnum value
        'is_active',     // Soft toggle without deletion
        'is_admin_only', // Hide from public-facing surfaces
        'config',        // JSON provider configuration (tokens, IDs, …)
        'metadata',      // JSON display metadata (icon, color, …)
    ];

    /**
     * Attribute casts.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type'          => ChannelTypeEnum::class,
        'is_active'     => 'boolean',
        'is_admin_only' => 'boolean',
        'config'        => 'array',
        'metadata'      => 'array',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /** All conversations routed through this channel. */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'channel_id');
    }

    /** Message templates available on this channel. */
    public function templates(): HasMany
    {
        return $this->hasMany(Template::class, 'channel_id');
    }

    /** Canned quick-replies available to agents. */
    public function quickReplies(): HasMany
    {
        return $this->hasMany(QuickReply::class, 'channel_id');
    }

    /** Incoming webhook payloads received on this channel (for debugging/audit). */
    public function webhookLogs(): HasMany
    {
        return $this->hasMany(WebhookLog::class, 'channel_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /** Channels that are turned on. */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Channels reserved for internal users only. */
    public function scopeAdminOnly($query)
    {
        return $query->where('is_admin_only', true);
    }

    /** Channels exposed to public chat surfaces. */
    public function scopePublic($query)
    {
        return $query->where('is_admin_only', false);
    }

    /** Filter by provider type (whatsapp/telegram/…). */
    public function scopeOfType($query, ChannelTypeEnum $type)
    {
        return $query->where('type', $type);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Set a single value inside the `config` JSON without losing the rest
     * of the structure. Uses Laravel's dot-notation helper.
     */
    public function setConfigValue(string $key, $value): self
    {
        $config = $this->config ?? [];
        data_set($config, $key, $value);
        $this->config = $config;

        return $this;
    }

    /**
     * True when the channel has enough configuration to actually send/receive.
     * Rules differ per provider: WhatsApp needs an access token + phone number
     * id, Telegram needs a bot token, webchat works out of the box.
     */
    public function isConfigured(): bool
    {
        return match ($this->type) {
            ChannelTypeEnum::WHATSAPP => ! empty($this->getConfigValue('access_token')) && ! empty($this->getConfigValue('phone_number_id')),
            ChannelTypeEnum::TELEGRAM => ! empty($this->getConfigValue('bot_token')),
            ChannelTypeEnum::WEBCHAT  => true, // WebChat doesn't require external config
            default                   => false,
        };
    }

    /** Read a nested value from the `config` JSON with a default fallback. */
    public function getConfigValue(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    /** Whether this channel type allows sending template messages. */
    public function supportsTemplates(): bool
    {
        return $this->type->supportsTemplates();
    }

    /** List of MessageTypeEnum values supported by this channel's driver. */
    public function getSupportedMessageTypes(): array
    {
        return $this->type->supportedMessageTypes();
    }
}
