<?php

namespace Modules\Booking\Livewire\Doctor;

use Carbon\Carbon;
use Livewire\Component;
use Modules\Booking\Actions\Availability\CreateAvailabilityAction;
use Modules\Booking\Actions\Availability\DeleteAvailabilityAction;
use Modules\Booking\Actions\Availability\UpdateAvailabilityAction;
use Modules\Booking\Actions\Calendar\GetCalendarEventsAction;
use Modules\Booking\Actions\RecurringSchedule\GenerateAvailabilitiesFromScheduleAction;
use Modules\Booking\Repository\Availability\AvailabilityInterface;
use Modules\Booking\Repository\RecurringSchedule\RecurringScheduleInterface;

class AvailabilityCalendar extends Component
{
    public $events = [];

    public $showModal = false;

    public $showGenerateModal = false;

    public $editingAvailabilityId = null;

    // Form fields for manual availability
    public $date = '';

    public $start_time = '';

    public $end_time = '';

    public $slot_duration = 30;

    public $consultation_fee = '';

    public $is_active = true;

    // Generate form fields
    public $generate_start_date = '';

    public $generate_end_date = '';

    // Calendar state
    public $currentStart = null;

    public $currentEnd = null;

    // Selected availability for view/edit
    public $selectedAvailability = null;

    public $showDetailsModal = false;

    protected $listeners = [
        'dateRangeChanged' => 'updateDateRange',
        'dateClicked' => 'openCreateModalForDate',
        'eventClicked' => 'handleEventClick',
        'refreshCalendar' => 'loadEvents',
    ];

    protected function rules(): array
    {
        return [
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_duration' => ['required', 'integer', 'min:5', 'max:240'],
            'consultation_fee' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function mount(): void
    {
        $this->currentStart = now()->startOfMonth()->format('Y-m-d');
        $this->currentEnd = now()->endOfMonth()->format('Y-m-d');
        $this->date = now()->format('Y-m-d');
        $this->generate_start_date = now()->format('Y-m-d');
        $this->generate_end_date = now()->addDays(14)->format('Y-m-d');
        $this->consultation_fee = config('booking.default_consultation_fee', 50);
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

        $this->events = $action->getAvailabilityEvents($doctorId, $startDate, $endDate);

        $this->dispatch('eventsUpdated', events: $this->events);
    }

    public function openCreateModalForDate($date = null): void
    {
        $this->resetForm();
        $this->date = $date ?? now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function handleEventClick($eventId): void
    {
        if (str_starts_with($eventId, 'availability_')) {
            $id = (int) str_replace('availability_', '', $eventId);
            $this->showAvailabilityDetails($id);
        }
    }

    public function showAvailabilityDetails(int $id): void
    {
        $repository = app(AvailabilityInterface::class);
        $availability = $repository->find($id);

        if ($availability->doctor_id !== auth('doctor')->id()) {
            return;
        }

        $this->selectedAvailability = $availability->toArray();
        $this->selectedAvailability['date_formatted'] = $availability->date->format('Y-m-d');
        $this->selectedAvailability['start_time_formatted'] = $availability->start_time->format('H:i');
        $this->selectedAvailability['end_time_formatted'] = $availability->end_time->format('H:i');
        $this->selectedAvailability['available_slots'] = $availability->getAvailableSlots();
        $this->selectedAvailability['has_bookings'] = $availability->hasActiveBookings();
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal(): void
    {
        $this->showDetailsModal = false;
        $this->selectedAvailability = null;
    }

    public function editAvailability(): void
    {
        if (! $this->selectedAvailability) {
            return;
        }

        $this->editingAvailabilityId = $this->selectedAvailability['id'];
        $this->date = $this->selectedAvailability['date_formatted'];
        $this->start_time = $this->selectedAvailability['start_time_formatted'];
        $this->end_time = $this->selectedAvailability['end_time_formatted'];
        $this->slot_duration = $this->selectedAvailability['slot_duration'];
        $this->consultation_fee = $this->selectedAvailability['consultation_fee'];
        $this->is_active = $this->selectedAvailability['is_active'];

        $this->closeDetailsModal();
        $this->showModal = true;
    }

    public function deleteAvailability(): void
    {
        if (! $this->selectedAvailability) {
            return;
        }

        if ($this->selectedAvailability['has_bookings']) {
            session()->flash('error', __('booking::calendar.cannot_delete_with_bookings'));

            return;
        }

        $action = app(DeleteAvailabilityAction::class);
        $action->handle($this->selectedAvailability['id']);

        $this->closeDetailsModal();
        $this->loadEvents();
        session()->flash('success', __('booking::calendar.availability_deleted'));
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'doctor_id' => auth('doctor')->id(),
            'date' => $this->date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'slot_duration' => $this->slot_duration,
            'consultation_fee' => $this->consultation_fee,
            'is_active' => $this->is_active,
            'is_recurring_generated' => false,
        ];

        if ($this->editingAvailabilityId) {
            $action = app(UpdateAvailabilityAction::class);
            $action->handle($this->editingAvailabilityId, $data);
            session()->flash('success', __('booking::calendar.availability_updated'));
        } else {
            $action = app(CreateAvailabilityAction::class);
            $action->handle($data);
            session()->flash('success', __('booking::calendar.availability_created'));
        }

        $this->closeModal();
        $this->loadEvents();
    }

    public function openGenerateModal(): void
    {
        $this->generate_start_date = now()->format('Y-m-d');
        $this->generate_end_date = now()->addDays(14)->format('Y-m-d');
        $this->showGenerateModal = true;
    }

    public function closeGenerateModal(): void
    {
        $this->showGenerateModal = false;
    }

    public function generateFromRecurring(): void
    {
        $this->validate([
            'generate_start_date' => ['required', 'date', 'after_or_equal:today'],
            'generate_end_date' => ['required', 'date', 'after:generate_start_date'],
        ]);

        $startDate = Carbon::parse($this->generate_start_date);
        $endDate = Carbon::parse($this->generate_end_date);

        if ($startDate->diffInDays($endDate) > 90) {
            $this->addError('generate_end_date', __('booking::recurring.validation.max_days_exceeded'));

            return;
        }

        $scheduleRepository = app(RecurringScheduleInterface::class);
        $activeSchedules = $scheduleRepository->getActiveByDoctor(auth('doctor')->id());

        if ($activeSchedules->isEmpty()) {
            session()->flash('error', __('booking::calendar.no_recurring_schedules'));
            $this->closeGenerateModal();

            return;
        }

        $action = app(GenerateAvailabilitiesFromScheduleAction::class);
        $result = $action->handle(auth('doctor')->id(), $startDate, $endDate);

        $this->closeGenerateModal();
        $this->loadEvents();

        session()->flash('success', __('booking::recurring.messages.generated', [
            'created' => $result['created'],
            'skipped' => $result['skipped'],
        ]));
    }

    protected function resetForm(): void
    {
        $this->editingAvailabilityId = null;
        $this->date = now()->format('Y-m-d');
        $this->start_time = '';
        $this->end_time = '';
        $this->slot_duration = 30;
        $this->consultation_fee = config('booking.default_consultation_fee', 50);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('booking::livewire.doctor.availability-calendar');
    }
}
