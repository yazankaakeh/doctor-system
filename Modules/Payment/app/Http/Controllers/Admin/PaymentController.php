<?php

namespace Modules\Payment\Http\Controllers\Admin;

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
        $payments = $this->repository->getPendingOfflinePayments();

        return view('payment::admin.payments.index', compact('payments'));
    }

    public function show(Payment $payment): View
    {
        $payment->load(['booking', 'booking.doctor', 'patient', 'media']);

        return view('payment::admin.payments.show', compact('payment'));
    }

    public function verify(
        Payment $payment,
        VerifyPaymentRequest $request,
        VerifyOfflinePaymentAction $action
    ): RedirectResponse {
        try {
            $action->handle(
                $payment->id,
                auth()->user(),
                $request->validated()['notes'] ?? null
            );

            return redirect()
                ->route('admin.payment.index')
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
        try {
            $action->reject(
                $payment->id,
                auth()->user(),
                $request->validated()['notes'] ?? null
            );

            return redirect()
                ->route('admin.payment.index')
                ->with('success', __('payment::payment.payment_rejected'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
