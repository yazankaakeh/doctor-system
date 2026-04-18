<?php

namespace Modules\Booking\Livewire\Doctor;

use Carbon\Carbon;
use Livewire\Component;
use Modules\Booking\Actions\Calendar\GetCalendarEventsAction;
use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Booking\Repository\Booking\BookingInterface;

class AppointmentCalendar extends Component
{
    public $events = [];
    public $statusFilter = null;
    public $showDetailsModal = false;
    public $selectedBooking = null;

    // Calendar state
    public $currentStart = null;
    public $currentEnd = null;

    protected $listeners = [
        'dateRangeChanged' => 'updateDateRange',
        'eventClicked' => 'handleEventClick',
        'refreshCalendar' => 'loadEvents',
    ];

    public function mount(): void
    {
        $this->currentStart = now()->startOfMonth()->format('Y-m-d');
        $this->currentEnd = now()->endOfMonth()->format('Y-m-d');
        $this->loadEvents();
    }

    public function updateDateRange($start, $end): void
    {
        $this->currentStart = $start;
        $this->currentEnd = $end;
        $this->loadEvents();
    }

    public function loadEvents(): void
    {
        $action = app(GetCalendarEventsAction::class);
        $doctorId = auth('doctor')->id();
        $startDate = Carbon::parse($this->currentStart);
        $endDate = Carbon::parse($this->currentEnd);

        $this->events = $action->getBookingEvents(
            $doctorId,
            $startDate,
            $endDate,
            $this->statusFilter
        );

        $this->dispatch('eventsUpdated', events: $this->events);
    }

    public function updatedStatusFilter(): void
    {
        $this->loadEvents();
    }

    public function handleEventClick($eventId): void
    {
        // Handle both 'booking_123' and '123' formats
        if (str_starts_with($eventId, 'booking_')) {
            $id = (int) str_replace('booking_', '', $eventId);
        } else {
            $id = (int) $eventId;
        }

        if ($id > 0) {
            $this->showBookingDetails($id);
        }
    }

    public function showBookingDetails(int $id): void
    {
        $repository = app(BookingInterface::class);
        $booking = $repository->find($id);

        if ($booking->doctor_id !== auth('doctor')->id()) {
            return;
        }

        $this->selectedBooking = [
            'id' => $booking->id,
            'patient_name' => $booking->patient?->name,
            'patient_phone' => $booking->patient?->phone,
            'patient_email' => $booking->patient?->email,
            'booking_date' => $booking->booking_date->format('Y-m-d'),
            'booking_date_formatted' => $booking->booking_date->translatedFormat('l, F j, Y'),
            'start_time' => $booking->start_time->format('H:i'),
            'end_time' => $booking->end_time->format('H:i'),
            'duration' => $booking->duration,
            'status' => $booking->status->value,
            'status_label' => $booking->status->label(),
            'status_class' => $booking->status->class(),
            'consultation_fee' => $booking->consultation_fee,
            'notes' => $booking->notes,
            'meeting_link' => $booking->meeting_link,
            'meeting_room_name' => $booking->meeting_room_name,
            'can_be_completed' => $booking->status === BookingStatusEnum::CONFIRMED,
            'can_be_marked_no_show' => $booking->status === BookingStatusEnum::CONFIRMED,
        ];

        $this->showDetailsModal = true;
    }

    public function closeDetailsModal(): void
    {
        $this->showDetailsModal = false;
        $this->selectedBooking = null;
    }

    public function completeBooking(): void
    {
        if (!$this->selectedBooking || !$this->selectedBooking['can_be_completed']) {
            return;
        }

        $repository = app(BookingInterface::class);
        $repository->update($this->selectedBooking['id'], [
            'status' => BookingStatusEnum::COMPLETED->value,
        ]);

        session()->flash('success', __('booking::calendar.booking_completed'));
        $this->closeDetailsModal();
        $this->loadEvents();
    }

    public function markNoShow(): void
    {
        if (!$this->selectedBooking || !$this->selectedBooking['can_be_marked_no_show']) {
            return;
        }

        $repository = app(BookingInterface::class);
        $repository->update($this->selectedBooking['id'], [
            'status' => BookingStatusEnum::NO_SHOW->value,
        ]);

        session()->flash('success', __('booking::calendar.booking_no_show'));
        $this->closeDetailsModal();
        $this->loadEvents();
    }

    public function getStatusOptionsProperty(): array
    {
        return [
            '' => __('booking::calendar.all_statuses'),
            BookingStatusEnum::PENDING->value => BookingStatusEnum::PENDING->label(),
            BookingStatusEnum::CONFIRMED->value => BookingStatusEnum::CONFIRMED->label(),
            BookingStatusEnum::CANCELLED->value => BookingStatusEnum::CANCELLED->label(),
            BookingStatusEnum::COMPLETED->value => BookingStatusEnum::COMPLETED->label(),
            BookingStatusEnum::NO_SHOW->value => BookingStatusEnum::NO_SHOW->label(),
        ];
    }

    public function render()
    {
        return view('booking::livewire.doctor.appointment-calendar');
    }
}
