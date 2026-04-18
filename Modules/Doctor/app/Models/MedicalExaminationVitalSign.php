<?php

namespace Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MedicalExaminationVitalSign extends Pivot
{
    protected $table = 'medical_examination_vital_sign';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'vital_sign_id',
        'medical_examination_id',
        'value',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    /**
     * Get the vital sign for this measurement.
     */
    public function vitalSign(): BelongsTo
    {
        return $this->belongsTo(VitalSign::class, 'vital_sign_id');
    }
}
