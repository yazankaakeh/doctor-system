<?php

/**
 * -----------------------------------------------------------------------------
 * Template Model
 * -----------------------------------------------------------------------------
 *
 * Represents a pre-approved message template (typically a WhatsApp Business
 * template, but the concept generalises to any channel that needs
 * provider-side moderation before sending).
 *
 * Structure follows the standard WhatsApp template layout:
 *   - Optional HEADER (text/image/video/document)
 *   - Mandatory BODY (supports {{1}} / {1} placeholders)
 *   - Optional FOOTER
 *   - Optional BUTTONS (call-to-action / quick-reply)
 *
 * `parameter_format` controls whether the body uses Meta's "{{1}}" syntax
 * or the app's native "{name}" syntax so the two can co-exist.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Messaging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Messaging\Enums\TemplateStatusEnum;

class Template extends Model
{
    protected $table = 'messaging_templates';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'channel_id',           // FK → messaging_channels.id
        'name',                 // Internal template name
        'external_template_id', // Provider-side id once approved (e.g. WhatsApp)
        'language',             // ISO language code (en, ar, tr, …)
        'category',             // marketing / utility / authentication
        'header_type',          // text / image / video / document / null
        'header_content',       // Header content or media URL
        'body',                 // Main body with {{1}} or {name} placeholders
        'footer',               // Optional footer text
        'buttons',              // JSON array of button definitions
        'parameter_format',     // "{{1}}" for Meta style, "{name}" for native
        'status',               // TemplateStatusEnum (pending/approved/rejected)
        'is_active',            // Soft toggle
    ];

    protected $casts = [
        'status' => TemplateStatusEnum::class,
        'buttons' => 'array',
        'is_active' => 'boolean',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /** Channel this template is registered against. */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }

    /** Ordered placeholder parameter definitions (for the edit UI). */
    public function parameters(): HasMany
    {
        return $this->hasMany(TemplateParameter::class, 'template_id')->orderBy('position');
    }

    /** Messages that were generated from this template. */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'template_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /** Only enabled templates. */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Only templates approved by the provider. */
    public function scopeApproved($query)
    {
        return $query->where('status', TemplateStatusEnum::APPROVED);
    }

    /** Templates that are both enabled AND approved (ready to send). */
    public function scopeAvailable($query)
    {
        return $query->active()->approved();
    }

    /** Restrict to a specific channel. */
    public function scopeForChannel($query, Channel|int $channel)
    {
        $channelId = $channel instanceof Channel ? $channel->id : $channel;

        return $query->where('channel_id', $channelId);
    }

    /** Restrict to templates in a given language. */
    public function scopeOfLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }

    /** Restrict to a category (marketing / utility / authentication). */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /** Combined "enabled + approved" check used before send. */
    public function canSend(): bool
    {
        return $this->is_active && $this->status->canSend();
    }

    /** Has a header section (text/image/video/document + content). */
    public function hasHeader(): bool
    {
        return ! empty($this->header_type) && ! empty($this->header_content);
    }

    /** Has a footer section. */
    public function hasFooter(): bool
    {
        return ! empty($this->footer);
    }

    /** Has at least one button configured. */
    public function hasButtons(): bool
    {
        return ! empty($this->buttons);
    }

    /**
     * Count unique placeholders found in the body. Used for validation so
     * callers know how many values they must supply.
     */
    public function getParameterCount(): int
    {
        // Pattern depends on which parameter syntax this template uses.
        $pattern = $this->parameter_format === '{{1}}'
            ? '/\{\{(\d+)\}\}/'
            : '/\{(\w+)\}/';

        preg_match_all($pattern, $this->body, $matches);

        return count(array_unique($matches[1]));
    }

    /**
     * Substitute $variables into the body and return the final string ready
     * for delivery. Honours `parameter_format` so both {{1}} and {name}
     * styles work.
     */
    public function buildContent(array $variables): string
    {
        $content = $this->body;

        foreach ($variables as $key => $value) {
            if ($this->parameter_format === '{{1}}') {
                $content = str_replace('{{'.$key.'}}', $value, $content);
            } else {
                $content = str_replace('{'.$key.'}', $value, $content);
            }
        }

        return $content;
    }

    /**
     * Build a plain-text preview that stitches header + body + footer.
     * Useful in list views that just need a readable summary of a template.
     */
    public function getFullPreview(): string
    {
        $parts = [];

        // Text headers are the only kind we can meaningfully preview as text.
        if ($this->hasHeader() && $this->header_type === 'text') {
            $parts[] = $this->header_content;
        }

        $parts[] = $this->body;

        if ($this->hasFooter()) {
            $parts[] = $this->footer;
        }

        return implode("\n\n", $parts);
    }
}
