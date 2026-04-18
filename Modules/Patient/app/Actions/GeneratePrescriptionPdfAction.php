<?php

namespace Modules\Patient\Actions;

use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Doctor\Models\MedicalExamination;

class GeneratePrescriptionPdfAction
{
    public function handle(int $examinationId, int $patientId)
    {
        $examination = MedicalExamination::query()
            ->where('patient_id', $patientId)
            ->with([
                'patient',
                'doctor',
                'clinic',
                'medicines',
                'finalDiagnosis',
                'vitalSigns',
                'medicalTests'
            ])
            ->findOrFail($examinationId);

        // Load pivot dosage forms
        $examination->medicines->each(function ($medicine) {
            $medicine->pivot->load('dosageForm');
        });

        return Pdf::loadView('patient::pdf.prescription', [
            'examination' => $examination,
        ])->setPaper('a4');
    }
}
