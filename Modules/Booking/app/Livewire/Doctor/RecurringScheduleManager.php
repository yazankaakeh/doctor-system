<?php

namespace Modules\Booking\Livewire\Doctor;

use Livewire\Component;
use Modules\Booking\Actions\RecurringSchedule\CreateRecurringScheduleAction;
use Modules\Booking\Actions\RecurringSchedule\DeleteRecurringScheduleAction;
use Modules\Booking\Actions\RecurringSchedule\UpdateRecurringScheduleAction;
use Modules\Booking\Repository\RecurringSchedule\RecurringScheduleInterface;

class RecurringScheduleManager extends Component
{
    public $schedules = [];

    public $showModal = false;

    public $editingScheduleId = null;

    // Form fields
    public $day_of_week = '';

    public $start_time = '';

    public $end_time = '';

    public $slot_duration = 30;

    public $consultation_fee = '';

    public $effective_from = '';

    public $effective_until = '';

    public $is_active = true;

    // Delete confirmation
    public $confirmingDelete = false;

    public $scheduleToDelete = null;

    protected $listeners = ['refreshSchedules' => 'loadSchedules'];

    protected function rules(): array
    {
        return [
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_duration' => ['required', 'integer', 'min:5', 'max:240'],
            'consultation_fee' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date', 'after_or_equal:today'],
            'effective_until' => ['nullable', 'date', 'after:effective_from'],
            'is_active' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'end_time.after' => __('booking::recurring.validation.end_time_after_start'),
            'effective_until.after' => __('booking::recurring.validation.effective_until_after'),
        ];
    }

    public function mount(): void
    {
        $this->loadSchedules();
        $this->effective_from = now()->format('Y-m-d');
    }

    public function loadSchedules(): void
    {
        $repository = app(RecurringScheduleInterface::class);
        $this->schedules = $repository->getByDoctor(auth('doctor')->id())->toArray();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $repository = app(RecurringScheduleInterface::class);
        $schedule = $repository->find($id);

        if ($schedule->doctor_id !== auth('doctor')->id()) {
            return;
        }

        $this->editingScheduleId = $id;
        $this->day_of_week = $schedule->day_of_week;
        $this->start_time = $schedule->start_time->format('H:i');
        $this->end_time = $schedule->end_time->format('H:i');
        $this->slot_duration = $schedule->slot_duration;
        $this->consultation_fee = $schedule->consultation_fee;
        $this->effective_from = $schedule->effective_from->format('Y-m-d');
        $this->effective_until = $schedule->effective_until?->format('Y-m-d');
        $this->is_active = $schedule->is_active;
        $this->showModal = true;
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
            'day_of_week' => $this->day_of_week,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'slot_duration' => $this->slot_duration,
            'consultation_fee' => $this->consultation_fee,
            'effective_from' => $this->effective_from,
            'effective_until' => $this->effective_until ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editingScheduleId) {
            $action = app(UpdateRecurringScheduleAction::class);
            $action->handle($this->editingScheduleId, $data);
            session()->flash('success', __('booking::recurring.messages.updated'));
        } else {
            $action = app(CreateRecurringScheduleAction::class);
            $action->handle($data);
            session()->flash('success', __('booking::recurring.messages.created'));
        }

        $this->closeModal();
        $this->loadSchedules();
    }

    public function toggleStatus(int $id): void
    {
        $repository = app(RecurringScheduleInterface::class);
        $schedule = $repository->find($id);

        if ($schedule->doctor_id !== auth('doctor')->id()) {
            return;
        }

        $action = app(UpdateRecurringScheduleAction::class);
        $action->handle($id, ['is_active' => ! $schedule->is_active]);

        $this->loadSchedules();
        session()->flash('success', $schedule->is_active
            ? __('booking::recurring.messages.deactivated')
            : __('booking::recurring.messages.activated'));
    }

    public function confirmDelete(int $id): void
    {
        $this->scheduleToDelete = $id;
        $this->confirmingDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->scheduleToDelete = null;
        $this->confirmingDelete = false;
    }

    public function delete(): void
    {
        if (! $this->scheduleToDelete) {
            return;
        }

        $repository = app(RecurringScheduleInterface::class);
        $schedule = $repository->find($this->scheduleToDelete);

        if ($schedule->doctor_id !== auth('doctor')->id()) {
            return;
        }

        $action = app(DeleteRecurringScheduleAction::class);
        $action->handle($this->scheduleToDelete);

        $this->cancelDelete();
        $this->loadSchedules();
        session()->flash('success', __('booking::recurring.messages.deleted'));
    }

    protected function resetForm(): void
    {
        $this->editingScheduleId = null;
        $this->day_of_week = '';
        $this->start_time = '';
        $this->end_time = '';
        $this->slot_duration = 30;
        $this->consultation_fee = '';
        $this->effective_from = now()->format('Y-m-d');
        $this->effective_until = '';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function getDaysProperty(): array
    {
        return [
            0 => __('booking::days.sunday'),
            1 => __('booking::days.monday'),
            2 => __('booking::days.tuesday'),
            3 => __('booking::days.wednesday'),
            4 => __('booking::days.thursday'),
            5 => __('booking::days.friday'),
            6 => __('booking::days.saturday'),
        ];
    }

    public function render()
    {
        return view('booking::livewire.doctor.recurring-schedule-manager');
    }
}
