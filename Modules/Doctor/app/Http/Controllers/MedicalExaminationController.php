<?php

namespace Modules\Doctor\Http\Controllers;

use App\Enum\Pagination;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Booking\Models\Booking;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Doctor\Enums\MedicalExaminationStatusEnum;
use Modules\Doctor\Http\Requests\MedicalExaminationRequest;
use Modules\Doctor\Models\Clinic;
use Modules\Doctor\Models\MedicalExamination;
use Modules\Doctor\Models\Patient;
use Modules\Doctor\Models\VitalSign;
use Modules\Doctor\Notifications\ExaminationCompletedNotification;

class MedicalExaminationController extends Controller
{
    public function submit(MedicalExaminationRequest $request)
    {
        $data = $request->validated();
        $data['status'] = MedicalExaminationStatusEnum::DONE->value;

        /** @var MedicalExamination|null $examination */
        $examination = MedicalExamination::query()
            ->with('patient', 'doctor')
            ->find($request->id);

        if (! $examination) {
            return redirect()->back()->with('error', 'Medical Examination not found');
        }

        $wasAlreadyDone = $examination->status instanceof MedicalExaminationStatusEnum
            ? $examination->status === MedicalExaminationStatusEnum::DONE
            : (int) $examination->status === MedicalExaminationStatusEnum::DONE->value;

        $examination->update($data);

        // Notify the patient only on the DONE transition so re-submitting
        // an already-finalized examination doesn't spam the patient.
        if (! $wasAlreadyDone && $examination->patient) {
            try {
                $examination->patient->notify(
                    new ExaminationCompletedNotification($examination->fresh(['doctor', 'patient']))
                );
            } catch (\Throwable $e) {
                // Notification failure must not block the doctor's workflow.
                Log::warning('Failed to dispatch ExaminationCompletedNotification', [
                    'examination_id' => $examination->id,
                    'patient_id' => $examination->patient_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Medical Examination updated successfully');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'patient_id',
            'status',
            'clinic_id',
            'from',
            'to',
        ]);

        $data = MedicalExamination::query()
            ->with(['patient', 'clinic'])
            ->where('doctor_id', auth()->id())
            ->when(
                $filters['patient_id'] ?? null,
                fn ($q, $v) => $q->where('patient_id', $v),
            )
            ->when(
                $filters['status'] ?? null,
                fn ($q, $v) => $q->where('status', $v),
            )
            ->when(
                $filters['clinic_id'] ?? null,
                fn ($q, $v) => $q->where('clinic_id', $v),
            )
            ->when(
                $filters['from'] ?? null,
                fn ($q, $v) => $q->whereDate('created_at', '>=', $v),
            )
            ->when(
                $filters['to'] ?? null,
                fn ($q, $v) => $q->whereDate('created_at', '<=', $v),
            )
            ->latest()
            ->paginate(Pagination::PAG->value)
            ->withQueryString();

        $patients = Patient::query()->orderBy('name')->pluck('name', 'id');
        $clinics = Clinic::getClinicSelect2();
        $statuses = MedicalExaminationStatusEnum::getAllEnumValuesKeysLabel();

        return view(
            'doctor::doctor.medicalExamination.index',
            compact('data', 'patients', 'clinics', 'statuses', 'filters'),
        );
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

        if (! $clinicId) {
            // Fallback to the first active clinic
            $clinic = Clinic::where('is_active', ActiveEnum::ACTIVE->value)
                ->first();

            if (! $clinic) {
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
            $booking = Booking::where([
                'patient_id' => $patient->id,
                'doctor_id' => auth()->id(),
            ])
                ->where('status', BookingStatusEnum::CONFIRMED)
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
     *
     * The blade view (`doctor::doctor.medicalExamination.show`) iterates a
     * paginated `$data` collection of VitalSign records (it also includes
     * the vitalSign create/edit modals), so we must provide that variable
     * or the view errors with "Undefined variable $data".
     *
     * We also look up the MedicalExamination by id for context so future
     * changes to the view can filter by the specific examination if needed.
     */
    public function show($id)
    {
        // Ensure the examination exists – 404 if the id is invalid.
        $medicalExamination = MedicalExamination::query()->findOrFail($id);

        // Paginated list of vital signs rendered in the table.
        // Mirrors VitalSignController::index() so the view (which reuses
        // the same modals) receives data in the expected shape.
        $data = VitalSign::query()->paginate(Pagination::PAG->value);

        return view(
            'doctor::doctor.medicalExamination.show',
            compact('data', 'medicalExamination'),
        );
    }
}
