<?php

/**
 * -----------------------------------------------------------------------------
 * TemplateParameter Model
 * -----------------------------------------------------------------------------
 *
 * One placeholder definition inside a Template's body.
 *
 * A parameter describes WHERE a runtime value should come from when the
 * template is actually sent:
 *
 *   - `source_type = 'static'`        → value is a fixed string
 *     (`default_value`).
 *   - `source_type = 'model_field'`   → read `source_field` from the context
 *                                        model (e.g. "patient.name").
 *   - `source_type = 'custom'`        → provided at call-time by the caller.
 *
 * `position` matches the numeric placeholder index (1, 2, 3, …) in the
 * template body so resolution order is deterministic.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Messaging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateParameter extends Model
{
    protected $table = 'messaging_template_parameters';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'template_id',   // FK → messaging_templates.id
        'name',          // Human label (shown in the template editor)
        'position',      // 1-based placeholder index inside the body
        'source_type',   // static | model_field | custom
        'source_model',  // FQCN of the context model (for model_field)
        'source_field',  // Dot-path inside the context model
        'default_value', // Fallback when the source fails to resolve
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /** Parent template. */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Resolve this parameter's value given an optional context model.
     *
     * - static     → returns `default_value`
     * - model_field→ returns the dot-path from the supplied model (fallback
     *                to default_value when the value is null)
     * - custom     → returns null; caller is expected to supply the value
     */
    public function resolveValue($model = null): ?string
    {
        // Static value → just echo the default.
        if ($this->source_type === 'static') {
            return $this->default_value;
        }

        // Model-driven → pull from the model using dot-notation.
        if ($this->source_type === 'model_field' && $model) {
            $value = data_get($model, $this->source_field);

            return $value ?? $this->default_value;
        }

        // Custom: value is supplied by calling code.
        if ($this->source_type === 'custom') {
            return null;
        }

        return $this->default_value;
    }

    /** Does this parameter need a model passed to resolveValue()? */
    public function requiresModel(): bool
    {
        return $this->source_type === 'model_field';
    }

    /**
     * Whether we have enough information to resolve automatically (no
     * runtime input required).
     */
    public function canAutoResolve(): bool
    {
        return $this->source_type === 'static' ||
            ($this->source_type === 'model_field' && ! empty($this->source_model));
    }
}
