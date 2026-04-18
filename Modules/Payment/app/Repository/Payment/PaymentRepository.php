<?php

namespace Modules\Payment\Repository\Payment;

use App\Enum\Pagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Payment\Enums\PaymentStatusEnum;
use Modules\Payment\Models\Payment;

class PaymentRepository implements PaymentInterface
{
    public function getPendingOfflinePayments(): LengthAwarePaginator
    {
        return Payment::query()
            ->where('status', PaymentStatusEnum::AWAITING_VERIFICATION)
            ->with(['booking', 'booking.doctor', 'patient', 'media'])
            ->orderBy('created_at', 'desc')
            ->paginate(Pagination::PAG->value);
    }

    public function getPendingPaymentsForDoctor(int $doctorId): LengthAwarePaginator
    {
        return Payment::query()
            ->where('status', PaymentStatusEnum::AWAITING_VERIFICATION)
            ->whereHas('booking', function ($query) use ($doctorId) {
                $query->where('doctor_id', $doctorId);
            })
            ->with(['booking', 'booking.patient', 'patient', 'media'])
            ->orderBy('created_at', 'desc')
            ->paginate(Pagination::PAG->value);
    }

    public function getForDoctor(int $doctorId, array $filters = []): LengthAwarePaginator
    {
        $query = Payment::query()
            ->whereHas('booking', function ($query) use ($doctorId) {
                $query->where('doctor_id', $doctorId);
            })
            ->with(['booking', 'booking.patient', 'patient', 'media', 'verifiedBy']);

        // Apply status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply payment method filter
        if (! empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        // Apply date range filter
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate(Pagination::PAG->value)
            ->appends($filters);
    }

    public function getForPatient(int $patientId): LengthAwarePaginator
    {
        return Payment::query()
            ->forPatient($patientId)
            ->with(['booking', 'booking.doctor'])
            ->orderBy('created_at', 'desc')
            ->paginate(Pagination::PAG->value);
    }

    public function store(array $data): Payment
    {
        return Payment::create($data);
    }

    public function find(int $id): Payment
    {
        return Payment::query()->findOrFail($id);
    }

    public function findByBooking(int $bookingId): ?Payment
    {
        return Payment::query()
            ->where('booking_id', $bookingId)
            ->first();
    }

    public function findByPayPalOrderId(string $paypalOrderId): ?Payment
    {
        return Payment::query()
            ->where('paypal_order_id', $paypalOrderId)
            ->first();
    }

    public function update(int $id, array $data): Payment
    {
        $payment = $this->find($id);
        $payment->update($data);

        return $payment;
    }
}
