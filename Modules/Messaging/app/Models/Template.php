<?php

namespace Modules\Messaging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Messaging\Enums\TemplateStatusEnum;

class Template extends Model
{
    protected $table = 'messaging_templates';

    protected $fillable = [
        'channel_id',
        'name',
        'external_template_id',
        'language',
        'category',
        'header_type',
        'header_content',
        'body',
        'footer',
        'buttons',
        'parameter_format',
        'status',
        'is_active',
    ];

    protected $casts = [
        'status' => TemplateStatusEnum::class,
        'buttons' => 'array',
        'is_active' => 'boolean',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }

    public function parameters(): HasMany
    {
        return $this->hasMany(TemplateParameter::class, 'template_id')->orderBy('position');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'template_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', TemplateStatusEnum::APPROVED);
    }

    public function scopeAvailable($query)
    {
        return $query->active()->approved();
    }

    public function scopeForChannel($query, Channel|int $channel)
    {
        $channelId = $channel instanceof Channel ? $channel->id : $channel;

        return $query->where('channel_id', $channelId);
    }

    public function scopeOfLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }

    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function canSend(): bool
    {
        return $this->is_active && $this->status->canSend();
    }

    public function hasHeader(): bool
    {
        return ! empty($this->header_type) && ! empty($this->header_content);
    }

    public function hasFooter(): bool
    {
        return ! empty($this->footer);
    }

    public function hasButtons(): bool
    {
        return ! empty($this->buttons);
    }

    /**
     * Get parameter count from the body text.
     */
    public function getParameterCount(): int
    {
        $pattern = $this->parameter_format === '{{1}}'
            ? '/\{\{(\d+)\}\}/'
            : '/\{(\w+)\}/';

        preg_match_all($pattern, $this->body, $matches);

        return count(array_unique($matches[1]));
    }

    /**
     * Build message content from template with variables.
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
     * Get the full template preview with all sections.
     */
    public function getFullPreview(): string
    {
        $parts = [];

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
