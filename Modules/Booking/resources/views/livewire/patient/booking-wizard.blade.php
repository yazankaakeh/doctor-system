<div>
    <!-- Progress Steps -->
    <div class="d-flex justify-content-between mb-4">
        <div class="step {{ $step >= 1 ? 'active' : '' }}">
            <span class="badge {{ $step >= 1 ? 'bg-primary' : 'bg-secondary' }} rounded-circle">1</span>
            <span class="ms-2">{{ __('booking::booking.step_1') }}</span>
        </div>
        <div class="step {{ $step >= 2 ? 'active' : '' }}">
            <span class="badge {{ $step >= 2 ? 'bg-primary' : 'bg-secondary' }} rounded-circle">2</span>
            <span class="ms-2">{{ __('booking::booking.step_2') }}</span>
        </div>
        <div class="step {{ $step >= 3 ? 'active' : '' }}">
            <span class="badge {{ $step >= 3 ? 'bg-primary' : 'bg-secondary' }} rounded-circle">3</span>
            <span class="ms-2">{{ __('booking::booking.step_3') }}</span>
        </div>
        <div class="step {{ $step >= 4 ? 'active' : '' }}">
            <span class="badge {{ $step >= 4 ? 'bg-primary' : 'bg-secondary' }} rounded-circle">4</span>
            <span class="ms-2">{{ __('booking::booking.step_4') }}</span>
        </div>
    </div>

    <!-- Step 1: Select Specialty -->
    @if($step === 1)
        <h5 class="mb-4">{{ __('booking::booking.select_specialty') }}</h5>
        <div class="row">
            @foreach($specialties as $specialty)
                <div class="col-md-4 mb-3">
                    <div class="card h-100 cursor-pointer specialty-card" wire:click="selectSpecialty({{ $specialty->id }})"
                         style="cursor: pointer;">
                        <div class="card-body text-center">
                            <i class="ti tabler-stethoscope mb-2" style="font-size: 2rem;"></i>
                            <h6 class="mb-0">{{ $specialty->name }}</h6>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Step 2: Choose Doctor -->
    @if($step === 2)
        <h5 class="mb-4">{{ __('booking::booking.select_doctor') }}</h5>
        <div class="row">
            @forelse($filteredDoctors as $doctor)
                <div class="col-md-6 mb-3">
                    <div class="card h-100" wire:click="selectDoctor({{ $doctor->id }})" style="cursor: pointer;">
                        <div class="card-body d-flex align-items-center">
                            <div class="avatar avatar-lg me-3">
                                <img src="{{ $doctor->getFirstMediaUrl('images') ?: asset('assets/img/avatars/3.png') }}"
                                     alt="Doctor" class="rounded-circle">
                            </div>
                            <div>
                                <h6 class="mb-0">Dr. {{ $doctor->name }}</h6>
                                <small class="text-muted">{{ $doctor->medicalSpecialty?->name }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">No doctors found for this specialty.</div>
                </div>
            @endforelse
        </div>
        <button class="btn btn-secondary mt-3" wire:click="previousStep">
            <i class="ti tabler-arrow-left me-1"></i>
            {{ __('booking::booking.back') }}
        </button>
    @endif

    <!-- Step 3: Pick Date & Time -->
    @if($step === 3)
        <h5 class="mb-4">{{ __('booking::booking.select_date') }}</h5>

        @if($availableDates->isEmpty())
            <div class="alert alert-warning">{{ __('booking::booking.no_available_slots') }}</div>
        @else
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="list-group">
                        @foreach($availableDates as $availability)
                            <button type="button"
                                    wire:click="selectDate('{{ $availability->date->format('Y-m-d') }}')"
                                    class="list-group-item list-group-item-action {{ $selectedDate === $availability->date->format('Y-m-d') ? 'active' : '' }}">
                                {{ $availability->date->format('l, F j, Y') }}
                                <span class="badge bg-success float-end">
                                    ${{ number_format($availability->consultation_fee, 2) }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                @if($selectedDate && count($availableSlots) > 0)
                    <div class="col-md-6">
                        <h6>{{ __('booking::booking.available_slots') }}</h6>
                        <div class="row">
                            @foreach($availableSlots as $slot)
                                <div class="col-4 mb-2">
                                    <button type="button"
                                            wire:click="selectSlot({{ $selectedAvailabilityId }}, '{{ $slot['start'] }}')"
                                            class="btn btn-outline-primary w-100">
                                        {{ $slot['start'] }}
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($selectedDate)
                    <div class="col-md-6">
                        <div class="alert alert-info">{{ __('booking::booking.no_available_slots') }}</div>
                    </div>
                @endif
            </div>
        @endif

        <button class="btn btn-secondary" wire:click="previousStep">
            <i class="ti tabler-arrow-left me-1"></i>
            {{ __('booking::booking.back') }}
        </button>
    @endif

    <!-- Step 4: Confirm & Pay -->
    @if($step === 4)
        <h5 class="mb-4">{{ __('booking::booking.booking_summary') }}</h5>

        @error('booking')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti tabler-alert-circle me-2"></i>
                {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @enderror

        @php
            $doctor = $doctors->firstWhere('id', $selectedDoctorId);
        @endphp

        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">{{ __('booking::booking.doctor') }}</h6>
                        <p><strong>Dr. {{ $doctor?->name }}</strong></p>
                        <p class="text-muted">{{ $doctor?->medicalSpecialty?->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">{{ __('booking::booking.booking_date') }}</h6>
                        <p><strong>{{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}</strong></p>
                        <p>{{ $selectedTime }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">{{ __('booking::booking.duration') }}</h6>
                        <p>{{ $selectedAvailability?->slot_duration }} minutes</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">{{ __('booking::booking.fee') }}</h6>
                        <h4 class="text-primary">${{ number_format($selectedAvailability?->consultation_fee ?? 0, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">{{ __('booking::booking.notes') }}</label>
            <textarea wire:model="notes" class="form-control" rows="3"
                      placeholder="{{ __('booking::booking.notes') }}"></textarea>
        </div>

        <div class="d-flex justify-content-between">
            <button class="btn btn-secondary" wire:click="previousStep">
                <i class="ti tabler-arrow-left me-1"></i>
                {{ __('booking::booking.back') }}
            </button>
            <button class="btn btn-primary" wire:click="confirmBooking" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="confirmBooking">
                    {{ __('booking::booking.confirm_booking') }}
                    <i class="ti tabler-arrow-right ms-1"></i>
                </span>
                <span wire:loading wire:target="confirmBooking">
                    <span class="spinner-border spinner-border-sm me-1"></span>
                    {{ __('booking::booking.processing') }}
                </span>
            </button>
        </div>
    @endif
</div>
