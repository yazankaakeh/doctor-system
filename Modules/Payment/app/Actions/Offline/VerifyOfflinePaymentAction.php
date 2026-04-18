<?php

namespace Modules\Payment\Actions\Offline;

use Modules\Booking\Actions\Booking\ConfirmBookingAction;
use Modules\Payment\Enums\PaymentStatusEnum;
use Modules\Payment\Models\Payment;
use Modules\Payment\Repository\Payment\PaymentInterface;

class VerifyOfflinePaymentAction
{
    public function __construct(
        private readonly PaymentInterface $repository,
        private readonly ConfirmBookingAction $confirmBookingAction
    ) {}

    public function handle(int $paymentId, object $verifier, ?string $notes = null): Payment
    {
        $payment = $this->repository->find($paymentId);

        if (! $payment->isAwaitingVerification()) {
            throw new \Exception(__('payment::payment.not_awaiting_verification'));
        }

        $payment = $this->repository->update($paymentId, [
            'status' => PaymentStatusEnum::COMPLETED,
            'verified_at' => now(),
            'verified_by_id' => $verifier->id,
            'verified_by_type' => get_class($verifier),
            'admin_notes' => $notes,
            'paid_at' => now(),
        ]);

        $this->confirmBookingAction->handle($payment->booking_id);

        return $payment;
    }

    public function reject(int $paymentId, object $verifier, ?string $notes = null): Payment
    {
        $payment = $this->repository->find($paymentId);

        if (! $payment->isAwaitingVerification()) {
            throw new \Exception(__('payment::payment.not_awaiting_verification'));
        }

        return $this->repository->update($paymentId, [
            'status' => PaymentStatusEnum::FAILED,
            'verified_at' => now(),
            'verified_by_id' => $verifier->id,
            'verified_by_type' => get_class($verifier),
            'admin_notes' => $notes,
        ]);
    }
}
