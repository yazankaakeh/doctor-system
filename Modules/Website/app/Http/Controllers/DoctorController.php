<?php

namespace Modules\Website\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\MedicalSpecialty;

class DoctorController extends Controller
{
    /**
     * Display list of all doctors.
     */
    public function index(Request $request): View
    {
        $specialties = MedicalSpecialty::query()
            ->where('is_active', 1)
            ->get();

        $query = Doctor::query()
            ->where('is_active', 1)
            ->with(['medicalSpecialty', 'media'])
            ->withCount(['medicalExaminations', 'bookings']);

        // Filter by specialty
        if ($request->filled('specialty')) {
            $query->where('medical_specialty_id', $request->specialty);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $doctors = $query->paginate(12);

        return view('website::doctors.index', compact('doctors', 'specialties'));
    }

    /**
     * Display individual doctor profile.
     */
    public function show(int $id): View
    {
        $doctor = Doctor::query()
            ->where('is_active', 1)
            ->with(['medicalSpecialty', 'media', 'availabilities' => function ($q) {
                $q->where('is_active', 1)
                    ->where('date', '>=', now()->toDateString())
                    ->orderBy('date')
                    ->limit(10);
            }])
            ->withCount(['medicalExaminations', 'bookings'])
            ->findOrFail($id);

        // Get statistics
        $stats = [
            'total_examinations' => $doctor->medicalExaminations()->count(),
            'total_patients' => $doctor->medicalExaminations()->distinct('patient_id')->count('patient_id'),
            'total_bookings' => $doctor->bookings()->count(),
            'completed_bookings' => $doctor->bookings()->where('status', 4)->count(), // Completed status
        ];

        // Get related doctors (same specialty)
        $relatedDoctors = Doctor::query()
            ->where('is_active', 1)
            ->where('id', '!=', $doctor->id)
            ->where('medical_specialty_id', $doctor->medical_specialty_id)
            ->with(['medicalSpecialty', 'media'])
            ->withCount('medicalExaminations')
            ->limit(4)
            ->get();

        $locale = app()->getLocale();

        return view('website::doctors.show', compact('doctor', 'stats', 'relatedDoctors', 'locale'));
    }
}
