<?php

namespace Modules\Payment\Http\Controllers\Doctor;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Payment\Actions\Offline\VerifyOfflinePaymentAction;
use Modules\Payment\Http\Requests\VerifyPaymentRequest;
use Modules\Payment\Models\Payment;
use Modules\Payment\Repository\Payment\PaymentInterface;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentInterface $repository
    ) {}

    public function index(): View
    {
        $doctorId = auth('doctor')->id();

        // Build filters from request
        $filters = [
            'status' => request('status'),
            'payment_method' => request('payment_method'),
            'date_from' => request('date_from'),
            'date_to' => request('date_to'),
        ];

        // Remove empty filters
        $filters = array_filter($filters, fn ($value) => ! empty($value));

        // Get all payments for bookings that belong to this doctor with filters
        $payments = $this->repository->getForDoctor($doctorId, $filters);

        return view('payment::doctor.payments.index', compact('payments', 'filters'));
    }

    public function show(Payment $payment): View
    {
        // Ensure payment belongs to this doctor's booking
        if ($payment->booking->doctor_id !== auth('doctor')->id()) {
            abort(403, 'Unauthorized access to this payment.');
        }

        $payment->load(['booking', 'booking.patient', 'patient', 'media']);

        return view('payment::doctor.payments.show', compact('payment'));
    }

    public function verify(
        Payment $payment,
        VerifyPaymentRequest $request,
        VerifyOfflinePaymentAction $action
    ): RedirectResponse {
        // Ensure payment belongs to this doctor's booking
        if ($payment->booking->doctor_id !== auth('doctor')->id()) {
            abort(403, 'Unauthorized access to this payment.');
        }

        try {
            $action->handle(
                $payment->id,
                auth('doctor')->user(),
                $request->validated()['notes'] ?? null
            );

            return redirect()
                ->route('doctor.payment.index')
                ->with('success', __('payment::payment.payment_verified'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function reject(
        Payment $payment,
        VerifyPaymentRequest $request,
        VerifyOfflinePaymentAction $action
    ): RedirectResponse {
        // Ensure payment belongs to this doctor's booking
        if ($payment->booking->doctor_id !== auth('doctor')->id()) {
            abort(403, 'Unauthorized access to this payment.');
        }

        try {
            $action->reject(
                $payment->id,
                auth('doctor')->user(),
                $request->validated()['notes'] ?? null
            );

            return redirect()
                ->route('doctor.payment.index')
                ->with('success', __('payment::payment.payment_rejected'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
