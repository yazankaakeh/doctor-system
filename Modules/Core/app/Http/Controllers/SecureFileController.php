<?php

namespace Modules\Core\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Doctor\Models\MedicalExamination;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SecureFileController extends Controller
{
    /**
     * Download a secure media file.
     * Requires authentication and validates ownership.
     */
    public function download(Request $request, int $mediaId): Response
    {
        $media = Media::findOrFail($mediaId);

        // Check if user is authenticated (doctor, patient, or admin)
        if (! auth('web')->check() && ! auth('doctor')->check() && ! auth()->check()) {
            abort(403, 'Unauthorized access to file.');
        }

        // Verify user has access to this file
        $this->authorizeFileAccess($media);

        // Get file path
        $disk = $media->disk;
        $path = $media->getPath();

        // Check if file exists
        if (! Storage::disk($disk)->exists($path)) {
            abort(404, 'File not found.');
        }

        // Get file content
        $file = Storage::disk($disk)->get($path);
        $mimeType = Storage::disk($disk)->mimeType($path);

        // Return file as response
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="'.$media->file_name.'"');
    }

    /**
     * Authorize file access based on model and user role.
     */
    private function authorizeFileAccess(Media $media): void
    {
        $modelType = $media->model_type;
        $model = $media->model;

        // Payment proofs - only patient who made payment, doctor who received it, or admin
        if ($modelType === 'Modules\Payment\Models\Payment') {
            $payment = $model;

            // Patient who made the payment
            if (auth('web')->check() && auth('web')->id() === $payment->patient_id) {
                return;
            }

            // Doctor who owns the booking
            if (auth('doctor')->check() && auth('doctor')->id() === $payment->booking->doctor_id) {
                return;
            }

            // Admin with permissions
            if (auth()->check() && auth()->user()->hasPermissionTo('manage payments')) {
                return;
            }

            abort(403, 'You do not have permission to access this file.');
        }

        // Medical examination files - only the doctor who created it or the patient
        if ($modelType === 'Modules\Doctor\Models\MedicalExamination') {
            $examination = $model;

            // Doctor who created the examination
            if (auth('doctor')->check() && auth('doctor')->id() === $examination->doctor_id) {
                return;
            }

            // Patient who owns the examination
            if (auth('web')->check() && auth('web')->id() === $examination->patient_id) {
                return;
            }

            abort(403, 'You do not have permission to access this file.');
        }

        // Medical test results (pivot table) - only the doctor or patient of the examination
        if ($modelType === 'Modules\Doctor\Models\MedicalExaminationMedicalTest') {
            $testPivot = $model;
            $examination = $testPivot->medicalExamination;

            // Doctor who created the examination
            if (auth('doctor')->check() && $examination && auth('doctor')->id() === $examination->doctor_id) {
                return;
            }

            // Patient who owns the examination
            if (auth('web')->check() && $examination && auth('web')->id() === $examination->patient_id) {
                return;
            }

            abort(403, 'You do not have permission to access this file.');
        }

        // Patient files - only the patient or their doctors
        if ($modelType === 'Modules\Doctor\Models\Patient') {
            $patient = $model;

            // The patient themselves
            if (auth('web')->check() && auth('web')->id() === $patient->id) {
                return;
            }

            // Doctor with access to this patient
            if (auth('doctor')->check()) {
                // Check if doctor has any examination with this patient
                $hasAccess = MedicalExamination::query()
                    ->where('doctor_id', auth('doctor')->id())
                    ->where('patient_id', $patient->id)
                    ->exists();

                if ($hasAccess) {
                    return;
                }
            }

            abort(403, 'You do not have permission to access this file.');
        }

        // Booking/Meeting recordings - only doctor, patient, or admin
        if ($modelType === 'Modules\Booking\Models\Booking') {
            $booking = $model;

            // Doctor who owns the booking
            if (auth('doctor')->check() && auth('doctor')->id() === $booking->doctor_id) {
                return;
            }

            // Patient who made the booking
            if (auth('web')->check() && auth('web')->id() === $booking->patient_id) {
                return;
            }

            // Admin
            if (auth()->check()) {
                return;
            }

            abort(403, 'You do not have permission to access this file.');
        }

        // Default: deny access
        abort(403, 'You do not have permission to access this file.');
    }
}
