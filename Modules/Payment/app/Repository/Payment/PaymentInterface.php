<?php

namespace Modules\Payment\Repository\Payment;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Payment\Models\Payment;

interface PaymentInterface
{
    public function getPendingOfflinePayments(): LengthAwarePaginator;

    public function getPendingPaymentsForDoctor(int $doctorId): LengthAwarePaginator;

    public function getForDoctor(int $doctorId, array $filters = []): LengthAwarePaginator;

    public function getForPatient(int $patientId): LengthAwarePaginator;

    public function store(array $data): Payment;

    public function find(int $id): Payment;

    public function findByBooking(int $bookingId): ?Payment;

    public function findByPayPalOrderId(string $paypalOrderId): ?Payment;

    public function update(int $id, array $data): Payment;
}
