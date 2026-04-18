<?php

namespace Modules\Messaging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Messaging\Enums\ChannelTypeEnum;

class Channel extends Model
{
    protected $table = 'messaging_channels';

    protected $fillable = [
        'name',
        'type',
        'is_active',
        'is_admin_only',
        'config',
        'metadata',
    ];

    protected $casts = [
        'type' => ChannelTypeEnum::class,
        'is_active' => 'boolean',
        'is_admin_only' => 'boolean',
        'config' => 'array',
        'metadata' => 'array',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'channel_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class, 'channel_id');
    }

    public function quickReplies(): HasMany
    {
        return $this->hasMany(QuickReply::class, 'channel_id');
    }

    public function webhookLogs(): HasMany
    {
        return $this->hasMany(WebhookLog::class, 'channel_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAdminOnly($query)
    {
        return $query->where('is_admin_only', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_admin_only', false);
    }

    public function scopeOfType($query, ChannelTypeEnum $type)
    {
        return $query->where('type', $type);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function setConfigValue(string $key, $value): self
    {
        $config = $this->config ?? [];
        data_set($config, $key, $value);
        $this->config = $config;

        return $this;
    }

    public function isConfigured(): bool
    {
        return match ($this->type) {
            ChannelTypeEnum::WHATSAPP => !empty($this->getConfigValue('access_token')) && !empty($this->getConfigValue('phone_number_id')),
            ChannelTypeEnum::TELEGRAM => !empty($this->getConfigValue('bot_token')),
            ChannelTypeEnum::WEBCHAT => true, // WebChat doesn't require external config
            default => false,
        };
    }

    public function getConfigValue(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    public function supportsTemplates(): bool
    {
        return $this->type->supportsTemplates();
    }

    public function getSupportedMessageTypes(): array
    {
        return $this->type->supportedMessageTypes();
    }
}
