<?php

namespace Modules\Payment\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Booking\Models\Booking;
use Modules\Payment\Actions\CreditCard\ProcessCreditCardPaymentAction;
use Modules\Payment\Http\Requests\CreditCardPaymentRequest;

class CreditCardController extends Controller
{
    /**
     * Display the credit-card payment form.
     */
    public function show(Booking $booking): View
    {
        if ($booking->patient_id !== auth('web')->id()) {
            abort(403);
        }

        if (! $booking->isPending()) {
            // Nothing to pay — bounce to the booking detail page
            return redirect()
                ->route('patient.bookings.show', $booking)
                ->with('info', __('payment::payment.credit_card.booking_not_payable'));
        }

        return view('payment::credit-card.show', compact('booking'));
    }

    /**
     * Charge the card (prototype) and confirm the booking on success.
     */
    public function submit(
        Booking $booking,
        CreditCardPaymentRequest $request,
        ProcessCreditCardPaymentAction $action,
    ): RedirectResponse {
        if ($booking->patient_id !== auth('web')->id()) {
            abort(403);
        }

        try {
            $action->handle($booking, $request->validated());

            return redirect()
                ->route('patient.bookings.show', $booking)
                ->with('success', __('payment::payment.credit_card.payment_success'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput($request->except(['card_number', 'cvv']))
                ->with('error', $e->getMessage());
        }
    }
}
