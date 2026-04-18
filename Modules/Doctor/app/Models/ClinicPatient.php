<?php

namespace Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ClinicPatient extends Pivot
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'clinic_id',
        'patient_id',
    ];
}
