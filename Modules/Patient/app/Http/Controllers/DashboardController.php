<?php

namespace Modules\Patient\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the patient dashboard.
     */
    public function index(): View
    {
        $patient = auth('web')->user();

        $data = [
            'totalAppointments' => $patient->medicalExamination()->count(),
            'upcomingAppointments' => $patient->medicalExamination()
                ->where('created_at', '>=', now()->startOfDay())
                ->count(),
            'totalClinics' => $patient->clinics()->count(),
            'recentExaminations' => $patient->medicalExamination()
                ->with(['clinic', 'medicines', 'medicalTests'])
                ->latest()
                ->take(5)
                ->get(),
        ];

        return view('patient::dashboard', compact('data', 'patient'));
    }
}
