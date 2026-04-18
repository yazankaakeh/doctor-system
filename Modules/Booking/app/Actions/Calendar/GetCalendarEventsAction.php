<?php

namespace Modules\Booking\Actions\Calendar;

use Carbon\Carbon;
use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\DoctorAvailability;
use Modules\Booking\Repository\Availability\AvailabilityInterface;
use Modules\Booking\Repository\Booking\BookingInterface;

class GetCalendarEventsAction
{
    public function __construct(
        protected AvailabilityInterface $availabilityRepository,
        protected BookingInterface $bookingRepository
    ) {}

    public function getAvailabilityEvents(int $doctorId, Carbon $startDate, Carbon $endDate): array
    {
        $availabilities = $this->availabilityRepository->getByDoctorForDateRange(
            $doctorId,
            $startDate,
            $endDate
        );

        return $availabilities->map(function (DoctorAvailability $availability) {
            $backgroundColor = $availability->is_recurring_generated ? '#28a745' : '#007bff';
            $borderColor = $availability->is_active ? $backgroundColor : '#6c757d';

            return [
                'id' => 'availability_'.$availability->id,
                'title' => $availability->start_time->format('H:i').' - '.$availability->end_time->format('H:i'),
                'start' => $availability->date->format('Y-m-d').'T'.$availability->start_time->format('H:i:s'),
                'end' => $availability->date->format('Y-m-d').'T'.$availability->end_time->format('H:i:s'),
                'backgroundColor' => $backgroundColor,
                'borderColor' => $borderColor,
                'extendedProps' => [
                    'type' => 'availability',
                    'availability_id' => $availability->id,
                    'slot_duration' => $availability->slot_duration,
                    'consultation_fee' => $availability->consultation_fee,
                    'is_recurring' => $availability->is_recurring_generated,
                    'is_active' => $availability->is_active,
                    'available_slots_count' => count($availability->getAvailableSlots()),
                ],
            ];
        })->toArray();
    }

    public function getBookingEvents(int $doctorId, Carbon $startDate, Carbon $endDate, ?int $statusFilter = null): array
    {
        $bookings = Booking::query()
            ->forDoctor($doctorId)
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->with(['patient', 'doctorAvailability'])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        return $bookings->map(function (Booking $booking) {
            $colors = $this->getBookingColors($booking->status);
            $patientAvatar = $booking->patient?->getFirstMediaUrl('images') ?: asset('assets/img/avatars/3.png');

            return [
                'id' => $booking->id,
                'title' => $booking->patient?->name ?? __('booking::calendar.unknown_patient'),
                'start' => $booking->booking_date->format('Y-m-d').'T'.$booking->start_time->format('H:i:s'),
                'end' => $booking->booking_date->format('Y-m-d').'T'.$booking->end_time->format('H:i:s'),
                'backgroundColor' => $colors['background'],
                'borderColor' => $colors['border'],
                'textColor' => $colors['text'],
                'status' => $booking->status->value,
                'statusLabel' => $booking->status->label(),
                'statusClass' => $booking->status->class(),
                'patientAvatar' => $patientAvatar,
                'duration' => $booking->duration,
                'fee' => number_format($booking->consultation_fee, 2),
                'extendedProps' => [
                    'type' => 'booking',
                    'booking_id' => $booking->id,
                    'patient_id' => $booking->patient_id,
                    'patient_name' => $booking->patient?->name,
                    'patient_phone' => $booking->patient?->phone,
                    'patient_email' => $booking->patient?->email,
                    'patient_avatar' => $patientAvatar,
                    'status' => $booking->status->value,
                    'status_label' => $booking->status->label(),
                    'status_class' => $booking->status->class(),
                    'duration' => $booking->duration,
                    'consultation_fee' => $booking->consultation_fee,
                    'fee_formatted' => number_format($booking->consultation_fee, 2),
                    'notes' => $booking->notes,
                    'meeting_link' => $booking->meeting_link,
                ],
            ];
        })->toArray();
    }

    protected function getBookingColors(BookingStatusEnum $status): array
    {
        return match ($status) {
            BookingStatusEnum::PENDING => [
                'background' => '#ffc107',
                'border' => '#e0a800',
                'text' => '#212529',
            ],
            BookingStatusEnum::CONFIRMED => [
                'background' => '#28a745',
                'border' => '#1e7e34',
                'text' => '#ffffff',
            ],
            BookingStatusEnum::CANCELLED => [
                'background' => '#dc3545',
                'border' => '#bd2130',
                'text' => '#ffffff',
            ],
            BookingStatusEnum::COMPLETED => [
                'background' => '#17a2b8',
                'border' => '#117a8b',
                'text' => '#ffffff',
            ],
            BookingStatusEnum::NO_SHOW => [
                'background' => '#6c757d',
                'border' => '#545b62',
                'text' => '#ffffff',
            ],
        };
    }
}
