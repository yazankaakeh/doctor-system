<?php

namespace Modules\Patient\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Modules\Patient\Actions\GeneratePrescriptionPdfAction;
use Modules\Patient\Actions\UploadTestResultAction;
use Modules\Patient\Http\Requests\UploadTestResultRequest;

class AppointmentController extends Controller
{
    /**
     * Display patient appointments.
     */
    public function index(): View
    {
        $patient = auth('web')->user();

        $appointments = $patient->medicalExamination()
            ->with(['clinic', 'medicines', 'medicalTests', 'vitalSigns', 'doctor'])
            ->latest()
            ->paginate(10);

        return view('patient::appointments.index', compact('appointments'));
    }

    /**
     * Display a specific appointment.
     */
    public function show(int $id): View
    {
        $patient = auth('web')->user();

        $appointment = $patient->medicalExamination()
            ->with([
                'clinic',
                'doctor',
                'medicines',
                'medicalTests',
                'vitalSigns',
                'finalDiagnosis'
            ])
            ->findOrFail($id);

        // Load pivot data with relationships
        $appointment->medicalTests->each(function ($test) {
            $test->pivot->load('media');
        });

        $appointment->medicines->each(function ($medicine) {
            $medicine->pivot->load('dosageForm');
        });

        return view('patient::appointments.show', compact('appointment'));
    }

    /**
     * Upload test result file
     */
    public function uploadTestResult(
        int $appointmentId,
        int $testPivotId,
        UploadTestResultRequest $request,
        UploadTestResultAction $action
    ): RedirectResponse {
        try {
            $patientId = auth('web')->id();

            $action->handle(
                $testPivotId,
                $request->file('test_result'),
                $patientId
            );

            return redirect()
                ->back()
                ->with('success', trans('patient::patient.test_result_uploaded'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', trans('patient::patient.test_result_upload_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Download prescription as PDF
     */
    public function downloadPrescription(
        int $id,
        GeneratePrescriptionPdfAction $action
    ) {
        $patientId = auth('web')->id();

        $pdf = $action->handle($id, $patientId);

        return $pdf->download('prescription-' . $id . '.pdf');
    }
}
