<?php

namespace Modules\Doctor\Http\Controllers;

use App\Enum\Pagination;
use App\Http\Controllers\Controller;
use Modules\Doctor\Enums\MedicalExaminationStatusEnum;
use Modules\Doctor\Http\Requests\MedicalExaminationRequest;
use Modules\Doctor\Models\MedicalExamination;
use Modules\Doctor\Models\Patient;

class MedicalExaminationController extends Controller
{
    public function submit(MedicalExaminationRequest $request)
    {
        $data = $request->validated();
        $data['status'] = MedicalExaminationStatusEnum::DONE->value;
        MedicalExamination::query()->where('id', $request->id)
            ->update($data);

        return redirect()->back()->with('success', 'Medical Examination updated successfully');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = MedicalExamination::query()->with('patient')->paginate(Pagination::PAG->value);

        return view('doctor::doctor.medicalExamination.index', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($patientId)
    {
        $patient = Patient::query()
            ->with('clinics')
            ->where(['id' => $patientId/* , 'is_active' => ActiveEnum::ACTIVE->value */])
            ->first();

        // Try to get clinic from patient first, otherwise use the first active clinic
        $clinicId = $patient?->clinics?->first()?->id;

        if (!$clinicId) {
            // Fallback to the first active clinic
            $clinic = \Modules\Doctor\Models\Clinic::where('is_active', \Modules\Core\App\Enums\ActiveEnum::ACTIVE->value)
                ->first();

            if (!$clinic) {
                return redirect()->back()->with('error', 'No active clinic found. Please contact the administrator.');
            }

            $clinicId = $clinic->id;
        }

        $medicalExamination = MedicalExamination::query()->updateOrCreate([
            'patient_id' => $patientId,
            'status' => MedicalExaminationStatusEnum::PENDING->value,
            'doctor_id' => auth()->id(),
        ], ['clinic_id' => $clinicId]);

        return redirect()->route('doctor.medicalExamination.create', $medicalExamination->id);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($medicalExaminationId)
    {
        /** @var MedicalExamination $medicalExamination */
        $medicalExamination = MedicalExamination::query()
            ->with('media')->with('patient')
            ->findOrFail($medicalExaminationId);
        $patient = $medicalExamination->patient;
        $medicalExaminations = MedicalExamination::patientMedicalExaminationsWithoutId(
            $patient->id,
            $medicalExamination->id,
        )->get();

        // Find active booking for this patient and doctor if Booking module exists
        $booking = null;
        if (class_exists('\Modules\Booking\Models\Booking')) {
            $booking = \Modules\Booking\Models\Booking::where([
                'patient_id' => $patient->id,
                'doctor_id' => auth()->id(),
            ])
            ->where('status', \Modules\Booking\Enums\BookingStatusEnum::CONFIRMED)
            ->latest()
            ->first();
        }

        return view(
            'doctor::doctor.medicalExamination.create',
            compact('patient', 'medicalExamination', 'medicalExaminations', 'booking'),
        );
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('doctor::doctor.medicalExamination.show');
    }
}
