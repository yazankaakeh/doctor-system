<?php

namespace Modules\Payment\Http\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Booking\Models\Booking;
use Modules\Payment\Actions\Offline\SubmitOfflinePaymentAction;
use Modules\Payment\Http\Requests\SubmitOfflinePaymentRequest;

class OfflinePaymentController extends Controller
{
    public function show(Booking $booking): View
    {
        if ($booking->patient_id !== auth('web')->id()) {
            abort(403, 'You are not authorized to access this booking.');
        }

        return view('payment::offline.upload-proof', compact('booking'));
    }

    public function submit(
        Booking                     $booking,
        SubmitOfflinePaymentRequest $request,
        SubmitOfflinePaymentAction  $action
    ): RedirectResponse
    {
        try {
            // Log for debugging
            \Log::info('Offline payment submission started', [
                'booking_id' => $booking->id,
                'patient_id' => auth('web')->id(),
                'files_count' => $request->file('proofs') ? count($request->file('proofs')) : 0,
            ]);

            $payment = $action->handle($booking, $request->file('proofs'));

            \Log::info('Offline payment submitted successfully', [
                'payment_id' => $payment->id,
            ]);

            return redirect()
                ->route('patient.dashboard')
                ->with('success', __('payment::payment.proof_submitted'));
        } catch (Exception $e) {
            \Log::error('Offline payment submission failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
