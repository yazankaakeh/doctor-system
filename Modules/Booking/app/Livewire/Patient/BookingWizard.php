<?php

namespace Modules\Booking\Livewire\Patient;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Booking\Actions\Booking\CreateBookingAction;
use Modules\Booking\Models\DoctorAvailability;

class BookingWizard extends Component
{
    public int $step = 1;

    public Collection $specialties;

    public Collection $doctors;

    public ?int $selectedSpecialtyId = null;

    public ?int $selectedDoctorId = null;

    public ?int $selectedAvailabilityId = null;

    public ?string $selectedDate = null;

    public ?string $selectedTime = null;

    public ?string $notes = null;

    public Collection $filteredDoctors;

    public Collection $availableDates;

    public array $availableSlots = [];

    public ?DoctorAvailability $selectedAvailability = null;

    public function mount(Collection $specialties, Collection $doctors): void
    {
        $this->specialties = $specialties;
        $this->doctors = $doctors;
        $this->filteredDoctors = collect();
        $this->availableDates = collect();
    }

    public function selectSpecialty(int $specialtyId): void
    {
        $this->selectedSpecialtyId = $specialtyId;
        $this->filteredDoctors = $this->doctors->where('medical_specialty_id', $specialtyId);
        $this->step = 2;
    }

    public function selectDoctor(int $doctorId): void
    {
        $this->selectedDoctorId = $doctorId;
        $this->loadAvailableDates();
        $this->step = 3;
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->loadAvailableSlots();
    }

    public function selectSlot(int $availabilityId, string $time): void
    {
        $this->selectedAvailabilityId = $availabilityId;
        $this->selectedTime = $time;
        $this->selectedAvailability = DoctorAvailability::find($availabilityId);
        $this->step = 4;
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function loadAvailableDates(): void
    {
        $this->availableDates = DoctorAvailability::query()
            ->forDoctor($this->selectedDoctorId)
            ->active()
            ->upcoming()
            ->orderBy('date')
            ->get()
            ->filter(fn (DoctorAvailability $a) => count($a->getAvailableSlots()) > 0)
            ->unique('date');
    }

    public function loadAvailableSlots(): void
    {
        $availability = DoctorAvailability::query()
            ->forDoctor($this->selectedDoctorId)
            ->active()
            ->where('date', $this->selectedDate)
            ->first();

        if ($availability) {
            $this->selectedAvailabilityId = $availability->id;
            $this->availableSlots = $availability->getAvailableSlots();
        } else {
            $this->availableSlots = [];
        }
    }

    public function confirmBooking(): void
    {
        $this->validate([
            'selectedAvailabilityId' => ['required', 'exists:doctor_availabilities,id'],
            'selectedDate' => ['required', 'date'],
            'selectedTime' => ['required'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $action = app(CreateBookingAction::class);
            $booking = $action->handle([
                'doctor_availability_id' => $this->selectedAvailabilityId,
                'booking_date' => $this->selectedDate,
                'start_time' => $this->selectedTime,
                'patient_id' => auth('web')->id(),
                'notes' => $this->notes,
            ]);

            session()->flash('success', __('booking::booking.booking_created'));
            $this->redirect(route('patient.bookings.show', $booking), navigate: false);
        } catch (\Exception $e) {
            $this->addError('booking', $e->getMessage());
        }
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('booking::livewire.patient.booking-wizard');
    }
}
