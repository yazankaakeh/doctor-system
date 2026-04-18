<?php

namespace Modules\Messaging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateParameter extends Model
{
    protected $table = 'messaging_template_parameters';

    protected $fillable = [
        'template_id',
        'name',
        'position',
        'source_type',
        'source_model',
        'source_field',
        'default_value',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Resolve the parameter value from a model instance.
     */
    public function resolveValue($model = null): ?string
    {
        // Static value - just return default
        if ($this->source_type === 'static') {
            return $this->default_value;
        }

        // Model field - get from the provided model
        if ($this->source_type === 'model_field' && $model) {
            $value = data_get($model, $this->source_field);

            return $value ?? $this->default_value;
        }

        // Custom - handled by calling code
        if ($this->source_type === 'custom') {
            return null;
        }

        return $this->default_value;
    }

    /**
     * Check if this parameter requires a model instance.
     */
    public function requiresModel(): bool
    {
        return $this->source_type === 'model_field';
    }

    /**
     * Check if this parameter can be auto-resolved.
     */
    public function canAutoResolve(): bool
    {
        return $this->source_type === 'static' ||
            ($this->source_type === 'model_field' && ! empty($this->source_model));
    }
}
