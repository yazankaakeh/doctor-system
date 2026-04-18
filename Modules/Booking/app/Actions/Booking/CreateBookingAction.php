<?php

namespace Modules\Booking\Actions\Booking;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\DoctorAvailability;
use Modules\Booking\Notifications\BookingCreatedPatientNotification;
use Modules\Booking\Notifications\NewBookingDoctorNotification;
use Modules\Booking\Repository\Booking\BookingInterface;

class CreateBookingAction
{
    public function __construct(
        private readonly BookingInterface $repository,
        private readonly CheckSlotAvailabilityAction $checkSlotAction
    ) {}

    public function handle(array $data): Booking
    {
        $booking = DB::transaction(function () use ($data) {
            $availability = DoctorAvailability::findOrFail($data['doctor_availability_id']);

            if (!$this->checkSlotAction->handle(
                $availability->doctor_id,
                $data['booking_date'],
                $data['start_time']
            )) {
                throw new \Exception(__('booking::booking.slot_not_available'));
            }

            $startTime = Carbon::parse($data['start_time']);
            $endTime = $startTime->copy()->addMinutes($availability->slot_duration);

            return $this->repository->store([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $availability->doctor_id,
                'doctor_availability_id' => $availability->id,
                'booking_date' => $data['booking_date'],
                'start_time' => $startTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'duration' => $availability->slot_duration,
                'consultation_fee' => $availability->consultation_fee,
                'status' => BookingStatusEnum::PENDING,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        // Send notifications after successful booking creation
        $this->sendNotifications($booking);

        return $booking;
    }

    /**
     * Send notifications to patient and doctor.
     */
    protected function sendNotifications(Booking $booking): void
    {
        // Reload relationships
        $booking->load(['patient', 'doctor', 'doctor.medicalSpecialty']);

        // Notify patient about the booking
        $booking->patient->notify(new BookingCreatedPatientNotification($booking));

        // Notify doctor about the new booking
        $booking->doctor->notify(new NewBookingDoctorNotification($booking));
    }
}
