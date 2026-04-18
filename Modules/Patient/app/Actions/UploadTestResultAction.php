<?php

namespace Modules\Patient\Actions;

use Illuminate\Http\UploadedFile;
use Modules\Doctor\Models\MedicalExaminationMedicalTest;

class UploadTestResultAction
{
    public function handle(int $testPivotId, UploadedFile $file, int $patientId): MedicalExaminationMedicalTest
    {
        $pivot = MedicalExaminationMedicalTest::query()
            ->whereHas('medicalExamination', function ($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            })
            ->findOrFail($testPivotId);

        // Add the file to the secure media collection
        $pivot->addMedia($file)
            ->toMediaCollection('attachment');

        return $pivot->fresh();
    }
}
