<?php

namespace Modules\Payment\Actions\PayPal;

use Modules\Booking\Models\Booking;
use Modules\Payment\Enums\PaymentMethodEnum;
use Modules\Payment\Enums\PaymentStatusEnum;
use Modules\Payment\Models\Payment;
use Modules\Payment\Repository\Payment\PaymentInterface;
use Modules\Payment\Services\PayPalService;

class CreatePayPalOrderAction
{
    public function __construct(
        private readonly PaymentInterface $repository,
        private readonly PayPalService $payPalService
    ) {}

    public function handle(Booking $booking): array
    {
        $existingPayment = $this->repository->findByBooking($booking->id);
        if ($existingPayment && $existingPayment->isCompleted()) {
            throw new \Exception(__('payment::payment.already_paid'));
        }

        $order = $this->payPalService->createOrder(
            $booking->consultation_fee,
            config('payment.currency', 'USD'),
            "Consultation with Dr. {$booking->doctor->name}"
        );

        $payment = $this->repository->store([
            'booking_id' => $booking->id,
            'patient_id' => $booking->patient_id,
            'payment_method' => PaymentMethodEnum::PAYPAL,
            'amount' => $booking->consultation_fee,
            'currency' => config('payment.currency', 'USD'),
            'status' => PaymentStatusEnum::PENDING,
            'paypal_order_id' => $order['id'],
        ]);

        return [
            'payment' => $payment,
            'approval_url' => $order['approval_url'],
        ];
    }
}
