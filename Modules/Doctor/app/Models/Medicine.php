<?php

namespace Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Doctor\Database\Factories\MedicineFactory;

class Medicine extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => ActiveEnum::class,
    ];

    public static function getMedicinesSelect2(): Collection
    {
        return Medicine::query()->pluck('name', 'id');
    }

    protected static function newFactory(): MedicineFactory
    {
        return MedicineFactory::new();
    }

    public function medicalExaminations(): BelongsToMany
    {
        return $this
            ->belongsToMany(MedicalExamination::class, 'medical_examination_medicine')
            ->withPivot(['dosage_form_id', 'dose', 'dosage', 'duration', 'note'])
            ->withTimestamps()
            ->using(MedicalExaminationMedicine::class);
    }
}
