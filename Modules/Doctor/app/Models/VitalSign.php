<?php

namespace Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Doctor\Database\Factories\VitalSignFactory;
use Spatie\Translatable\HasTranslations;

class VitalSign extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = ['name'];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'min_value',
        'max_value',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'is_active' => ActiveEnum::class,
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
    ];

    public static function getVitalSignsSelect2(): Collection
    {
        return VitalSign::query()->pluck('name', 'id');
    }

    protected static function newFactory(): VitalSignFactory
    {
        return VitalSignFactory::new();
    }

    public function medicalExaminations(): BelongsToMany
    {
        return $this
            ->belongsToMany(MedicalExamination::class, 'medical_examination_vital_sign')
            ->withPivot('value')
            ->withTimestamps()
            ->using(MedicalExaminationVitalSign::class); // optional
    }

    /**
     * Get the normal range display string.
     */
    public function getNormalRangeAttribute(): ?string
    {
        if ($this->min_value === null && $this->max_value === null) {
            return null;
        }

        $unit = $this->unit ? " {$this->unit}" : '';

        if ($this->min_value !== null && $this->max_value !== null) {
            return "{$this->min_value} - {$this->max_value}{$unit}";
        }

        if ($this->min_value !== null) {
            return ">= {$this->min_value}{$unit}";
        }

        return "<= {$this->max_value}{$unit}";
    }

    /**
     * Check if a given value is within the normal range.
     */
    public function isValueInRange(float|int|null $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        if ($this->min_value === null && $this->max_value === null) {
            return null;
        }

        $inRange = true;

        if ($this->min_value !== null && $value < $this->min_value) {
            $inRange = false;
        }

        if ($this->max_value !== null && $value > $this->max_value) {
            $inRange = false;
        }

        return $inRange;
    }
}
