<?php

namespace Modules\Patient\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class MedicalHistoryController extends Controller
{
    /**
     * Display patient medical history.
     */
    public function index(): View
    {
        $patient = auth('web')->user();

        $examinations = $patient->medicalExamination()
            ->with(['clinic', 'medicines', 'medicalTests', 'vitalSigns', 'finalDiagnosis'])
            ->latest()
            ->paginate(10);

        return view('patient::medical-history.index', compact('examinations', 'patient'));
    }
}
