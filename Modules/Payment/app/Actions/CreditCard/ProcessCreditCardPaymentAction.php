<?php

namespace Modules\Payment\Actions\CreditCard;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Booking\Actions\Booking\ConfirmBookingAction;
use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Booking\Models\Booking;
use Modules\Payment\Enums\PaymentMethodEnum;
use Modules\Payment\Enums\PaymentStatusEnum;
use Modules\Payment\Models\Payment;
use Modules\Payment\Repository\Payment\PaymentInterface;

/**
 * Prototype credit-card processor.
 *
 * This is NOT a real gateway integration — it exists so QA can exercise the
 * "pay → confirm booking" flow end-to-end without needing a real acquirer.
 * Any Luhn-valid card number is accepted. The only rejection path is if the
 * test card has been explicitly blacklisted (see `$declinedTestCards`).
 */
class ProcessCreditCardPaymentAction
{
    /**
     * Test cards that are intentionally rejected so QA can exercise the
     * "payment failed" branch. Any other Luhn-valid number succeeds.
     */
    protected array $declinedTestCards = [
        '4000000000000002', // Stripe/Adyen classic "generic decline" tester
    ];

    public function __construct(
        private readonly PaymentInterface $repository,
        private readonly ConfirmBookingAction $confirmBooking,
    ) {}

    /**
     * @param  array{cardholder_name:string,card_number:string,expiry_month:string,expiry_year:string,cvv:string}  $data
     *
     * @throws \Exception  if the booking is already paid, is not pending, or the test card is declined
     */
    public function handle(Booking $booking, array $data): Payment
    {
        return DB::transaction(function () use ($booking, $data) {

            // Guard: booking must belong to the authenticated patient (controller also checks this)
            if ($booking->status !== BookingStatusEnum::PENDING) {
                throw new \Exception(__('payment::payment.credit_card.booking_not_payable'));
            }

            // Guard: don't double-charge if a completed payment already exists
            $existing = $this->repository->findByBooking($booking->id);
            if ($existing && $existing->isCompleted()) {
                throw new \Exception(__('payment::payment.already_paid'));
            }

            $cardNumber = preg_replace('/\D+/', '', (string) $data['card_number']);

            // Prototype "decline" path for QA
            if (in_array($cardNumber, $this->declinedTestCards, true)) {
                $this->repository->store([
                    'booking_id'      => $booking->id,
                    'patient_id'      => $booking->patient_id,
                    'payment_method'  => PaymentMethodEnum::CREDIT_CARD,
                    'amount'          => $booking->consultation_fee,
                    'currency'        => config('payment.currency', 'USD'),
                    'status'          => PaymentStatusEnum::FAILED,
                    'payment_details' => [
                        'card_last4' => substr($cardNumber, -4),
                        'card_brand' => $this->detectBrand($cardNumber),
                        'gateway'    => 'prototype',
                        'decline_reason' => 'test_card_declined',
                    ],
                ]);
                throw new \Exception(__('payment::payment.credit_card.declined'));
            }

            // Create a COMPLETED payment — this is the prototype "authorize & capture" step
            $payment = $this->repository->store([
                'booking_id'      => $booking->id,
                'patient_id'      => $booking->patient_id,
                'payment_method'  => PaymentMethodEnum::CREDIT_CARD,
                'amount'          => $booking->consultation_fee,
                'currency'        => config('payment.currency', 'USD'),
                'status'          => PaymentStatusEnum::COMPLETED,
                'transaction_id'  => 'CC-TEST-' . strtoupper(Str::random(14)),
                'paid_at'         => now(),
                'payment_details' => [
                    'card_last4'       => substr($cardNumber, -4),
                    'card_brand'       => $this->detectBrand($cardNumber),
                    'cardholder_name'  => $data['cardholder_name'],
                    'expiry'           => $data['expiry_month'] . '/' . substr($data['expiry_year'], -2),
                    'gateway'          => 'prototype',
                    'captured_at'      => now()->toIso8601String(),
                ],
            ]);

            // Move the booking to CONFIRMED, create the meeting room, fire notifications
            $this->confirmBooking->handle($booking->id);

            return $payment->fresh();
        });
    }

    /**
     * Naive card-brand detector. Prototype-only, covers the major BIN ranges.
     */
    protected function detectBrand(string $number): string
    {
        if (preg_match('/^4\d{12}(\d{3})?(\d{3})?$/', $number)) return 'visa';
        if (preg_match('/^(5[1-5]\d{14}|2(2[2-9][1-9]|[3-6]\d{2}|7([01]\d|20))\d{12})$/', $number)) return 'mastercard';
        if (preg_match('/^3[47]\d{13}$/', $number)) return 'amex';
        if (preg_match('/^6(?:011|5\d{2})\d{12}$/', $number)) return 'discover';
        return 'unknown';
    }
}
