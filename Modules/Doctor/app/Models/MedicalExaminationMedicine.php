<?php

namespace Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MedicalExaminationMedicine extends Pivot
{
    protected $table = 'medical_examination_medicine';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'medicine_id',
        'medical_examination_id',
        'dosage_form_id',
        'dose',
        'dosage',
        'duration',
        'note',
    ];

    /**
     * Get the dosage form for this medicine prescription.
     */
    public function dosageForm(): BelongsTo
    {
        return $this->belongsTo(DosageForm::class, 'dosage_form_id');
    }
}
