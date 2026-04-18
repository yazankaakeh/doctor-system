<?php

namespace Modules\Payment\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Booking\Models\Booking;
use Modules\Payment\Actions\PayPal\CapturePayPalPaymentAction;
use Modules\Payment\Actions\PayPal\CreatePayPalOrderAction;

class PayPalController extends Controller
{
    public function create(
        Booking $booking,
        CreatePayPalOrderAction $action
    ): RedirectResponse {
        if ($booking->patient_id !== auth('web')->id()) {
            abort(403, 'You are not authorized to access this booking.');
        }

        try {
            $result = $action->handle($booking);

            return redirect()->away($result['approval_url']);
        } catch (\Exception $e) {
            return redirect()
                ->route('patient.dashboard')
                ->with('error', $e->getMessage());
        }
    }

    public function success(
        Request $request,
        CapturePayPalPaymentAction $action
    ): View|RedirectResponse {
        $token = $request->query('token');

        if (!$token) {
            return redirect()
                ->route('patient.dashboard')
                ->with('error', __('payment::payment.invalid_token'));
        }

        try {
            $payment = $action->handle($token);

            return view('payment::success', [
                'payment' => $payment,
                'booking' => $payment->booking,
            ]);
        } catch (\Exception $e) {
            return redirect()
                ->route('patient.dashboard')
                ->with('error', $e->getMessage());
        }
    }

    public function cancel(): RedirectResponse
    {
        return redirect()
            ->route('patient.dashboard')
            ->with('warning', __('payment::payment.payment_cancelled'));
    }
}
