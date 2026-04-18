<?php

namespace Modules\Booking\Livewire;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Modules\Auth\Actions\Patient\RegisterAction;
use Modules\Booking\Actions\Booking\CreateBookingAction;
use Modules\Booking\Models\DoctorAvailability;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\MedicalSpecialty;
use Modules\Doctor\Models\Patient;

class PublicBookingWizard extends Component
{
    // Wizard state
    public int $step = 1;
    public int $totalSteps = 5;

    // Selection data
    public ?int $selectedSpecialtyId = null;
    public ?int $selectedDoctorId = null;
    public ?int $selectedAvailabilityId = null;
    public ?string $selectedDate = null;
    public ?string $selectedTime = null;
    public ?string $notes = null;

    // Filtered/loaded data
    public Collection $specialties;
    public Collection $doctors;
    public Collection $filteredDoctors;
    public Collection $availableDates;
    public array $availableSlots = [];
    public ?DoctorAvailability $selectedAvailability = null;

    // Calendar state
    public int $calendarMonth;
    public int $calendarYear;

    // Auth mode: 'login' or 'register'
    public string $authMode = 'login';

    // Login form
    public string $loginEmail = '';
    public string $loginPassword = '';
    public bool $remember = false;

    // Register form
    public string $registerName = '';
    public string $registerEmail = '';
    public string $registerPhone = '';
    public string $registerPassword = '';
    public string $registerPassword_confirmation = '';

    // Error handling
    public ?string $authError = null;

    public function mount(): void
    {
        $this->specialties = MedicalSpecialty::query()
            ->where('is_active', 1)
            ->get();

        $this->doctors = Doctor::query()
            ->where('is_active', 1)
            ->with(['medicalSpecialty', 'media'])
            ->get();

        $this->filteredDoctors = collect();
        $this->availableDates = collect();

        // Initialize calendar to current month
        $this->calendarMonth = now()->month;
        $this->calendarYear = now()->year;

        // Check if user is already logged in
        if (Auth::guard('web')->check()) {
            $this->totalSteps = 4;
        }
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

    public function goToStep(int $step): void
    {
        if ($step < $this->step && $step >= 1) {
            $this->step = $step;
        }
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

    public function proceedToAuth(): void
    {
        $this->validate([
            'selectedAvailabilityId' => ['required', 'exists:doctor_availabilities,id'],
            'selectedDate' => ['required', 'date'],
            'selectedTime' => ['required'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // If already logged in, complete booking directly
        if (Auth::guard('web')->check()) {
            $this->completeBooking();

            return;
        }

        // Otherwise, go to auth step
        $this->step = 5;
    }

    public function setAuthMode(string $mode): void
    {
        $this->authMode = $mode;
        $this->authError = null;
        $this->resetValidation();
    }

    public function login(): void
    {
        $this->authError = null;

        $this->validate([
            'loginEmail' => ['required', 'email'],
            'loginPassword' => ['required', 'string'],
        ]);

        $patient = Patient::where('email', $this->loginEmail)->first();

        if (! $patient || ! Hash::check($this->loginPassword, $patient->password)) {
            $this->authError = __('auth::auth.failed');

            return;
        }

        if (! $patient->is_active) {
            $this->authError = __('auth::auth.inactive');

            return;
        }

        Auth::guard('web')->login($patient, $this->remember);
        session()->regenerate();

        $this->completeBooking();
    }

    public function register(): void
    {
        $this->authError = null;

        $this->validate([
            'registerName' => ['required', 'string', 'max:255'],
            'registerEmail' => ['required', 'string', 'email', 'max:255', 'unique:patients,email'],
            'registerPhone' => ['required', 'string', 'max:20', 'unique:patients,phone'],
            'registerPassword' => ['required', 'string', 'confirmed', Password::defaults()],
        ], [], [
            'registerName' => __('validation.attributes.name'),
            'registerEmail' => __('validation.attributes.email'),
            'registerPhone' => __('validation.attributes.phone'),
            'registerPassword' => __('validation.attributes.password'),
            'registerPassword_confirmation' => __('validation.attributes.password_confirmation'),
        ]);

        try {
            $action = app(RegisterAction::class);
            $action->handle([
                'name' => $this->registerName,
                'email' => $this->registerEmail,
                'phone' => $this->registerPhone,
                'password' => $this->registerPassword,
            ]);

            $this->completeBooking();
        } catch (\Exception $e) {
            $this->authError = $e->getMessage();
        }
    }

    protected function completeBooking(): void
    {
        try {
            $action = app(CreateBookingAction::class);
            $booking = $action->handle([
                'doctor_availability_id' => $this->selectedAvailabilityId,
                'booking_date' => $this->selectedDate,
                'start_time' => $this->selectedTime,
                'patient_id' => Auth::guard('web')->id(),
                'notes' => $this->notes,
            ]);

            session()->flash('success', __('booking::booking.booking_created'));
            $this->redirect(route('patient.bookings.show', $booking), navigate: false);
        } catch (\Exception $e) {
            $this->addError('booking', $e->getMessage());
        }
    }

    public function getSelectedDoctorProperty(): ?Doctor
    {
        return $this->doctors->firstWhere('id', $this->selectedDoctorId);
    }

    public function getSelectedSpecialtyProperty(): ?MedicalSpecialty
    {
        return $this->specialties->firstWhere('id', $this->selectedSpecialtyId);
    }

    public function getIsLoggedInProperty(): bool
    {
        return Auth::guard('web')->check();
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1)->subMonth();
        $this->calendarMonth = $date->month;
        $this->calendarYear = $date->year;
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1)->addMonth();
        $this->calendarMonth = $date->month;
        $this->calendarYear = $date->year;
    }

    public function getCalendarDaysProperty(): array
    {
        $firstDay = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1);
        $lastDay = $firstDay->copy()->endOfMonth();
        $startOfWeek = $firstDay->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = $lastDay->copy()->endOfWeek(Carbon::SATURDAY);

        $days = [];
        $current = $startOfWeek->copy();

        // Get available dates as array of strings for quick lookup
        $availableDateStrings = $this->availableDates->pluck('date')->map(fn ($d) => $d->format('Y-m-d'))->toArray();

        while ($current <= $endOfWeek) {
            $dateStr = $current->format('Y-m-d');
            $days[] = [
                'date' => $dateStr,
                'day' => $current->day,
                'isCurrentMonth' => $current->month === $this->calendarMonth,
                'isToday' => $current->isToday(),
                'isPast' => $current->isPast() && ! $current->isToday(),
                'isAvailable' => in_array($dateStr, $availableDateStrings),
                'isSelected' => $this->selectedDate === $dateStr,
            ];
            $current->addDay();
        }

        return $days;
    }

    public function getCalendarMonthNameProperty(): string
    {
        return Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1)->format('F Y');
    }

    public function getCanGoPreviousMonthProperty(): bool
    {
        $firstOfMonth = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1);

        return $firstOfMonth->isAfter(now()->startOfMonth());
    }

    public function render(): View
    {
        return view('booking::livewire.public-booking-wizard');
    }
}
