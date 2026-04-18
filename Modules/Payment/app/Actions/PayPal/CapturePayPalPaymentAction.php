<?php

namespace Modules\Payment\Actions\PayPal;

use Modules\Booking\Actions\Booking\ConfirmBookingAction;
use Modules\Payment\Enums\PaymentStatusEnum;
use Modules\Payment\Models\Payment;
use Modules\Payment\Repository\Payment\PaymentInterface;
use Modules\Payment\Services\PayPalService;

class CapturePayPalPaymentAction
{
    public function __construct(
        private readonly PaymentInterface $repository,
        private readonly PayPalService $payPalService,
        private readonly ConfirmBookingAction $confirmBookingAction
    ) {}

    public function handle(string $paypalOrderId): Payment
    {
        $payment = $this->repository->findByPayPalOrderId($paypalOrderId);

        if (!$payment) {
            throw new \Exception(__('payment::payment.not_found'));
        }

        if ($payment->isCompleted()) {
            return $payment;
        }

        $captureResult = $this->payPalService->captureOrder($paypalOrderId);

        if ($captureResult['status'] === 'COMPLETED') {
            $payment = $this->repository->update($payment->id, [
                'status' => PaymentStatusEnum::COMPLETED,
                'transaction_id' => $captureResult['transaction_id'] ?? null,
                'payment_details' => $captureResult,
                'paid_at' => now(),
            ]);

            $this->confirmBookingAction->handle($payment->booking_id);
        } else {
            $this->repository->update($payment->id, [
                'status' => PaymentStatusEnum::FAILED,
                'payment_details' => $captureResult,
            ]);

            throw new \Exception(__('payment::payment.capture_failed'));
        }

        return $payment;
    }
}
