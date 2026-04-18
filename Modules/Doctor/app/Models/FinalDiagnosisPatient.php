<?php

namespace Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class FinalDiagnosisPatient extends Pivot
{
    protected $table = 'final_diagnosis_patients';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'patient_id',
        'medical_examination_id',
        'final_diagnosis_id',
    ];

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class, 'id', 'patient_id');
    }

    public function medicalExamination(): HasOne
    {
        return $this->hasOne(MedicalExamination::class, 'id', 'medical_examination_id');
    }

    public function finalDiagnose(): HasOne
    {
        return $this->hasOne(FinalDiagnosis::class, 'id', 'final_diagnosis_id');
    }
}
