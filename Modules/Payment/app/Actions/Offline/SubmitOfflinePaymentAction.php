<?php

namespace Modules\Payment\Actions\Offline;

use Illuminate\Support\Facades\DB;
use Modules\Booking\Models\Booking;
use Modules\Payment\Enums\PaymentMethodEnum;
use Modules\Payment\Enums\PaymentStatusEnum;
use Modules\Payment\Models\Payment;
use Modules\Payment\Repository\Payment\PaymentInterface;

class SubmitOfflinePaymentAction
{
    public function __construct(
        private readonly PaymentInterface $repository
    ) {}

    public function handle(Booking $booking, ?array $proofFiles): Payment
    {
        return DB::transaction(function () use ($booking, $proofFiles) {
            $existingPayment = $this->repository->findByBooking($booking->id);
            if ($existingPayment && $existingPayment->isCompleted()) {
                throw new \Exception(__('payment::payment.already_paid'));
            }

            // Check if already awaiting verification
            if ($existingPayment && $existingPayment->isAwaitingVerification()) {
                throw new \Exception(__('payment::payment.already_submitted'));
            }

            $payment = $this->repository->store([
                'booking_id' => $booking->id,
                'patient_id' => $booking->patient_id,
                'payment_method' => PaymentMethodEnum::OFFLINE,
                'amount' => $booking->consultation_fee,
                'currency' => config('payment.currency', 'USD'),
                'status' => PaymentStatusEnum::AWAITING_VERIFICATION,
            ]);

            // Store proof files using Spatie Media Library
            if ($proofFiles && is_array($proofFiles)) {
                foreach ($proofFiles as $file) {
                    if ($file && $file->isValid()) {
                        $payment->addMedia($file)
                            ->usingFileName($file->hashName())
                            ->toMediaCollection('payment_proofs');
                    }
                }
            }

            return $payment;
        });
    }
}
