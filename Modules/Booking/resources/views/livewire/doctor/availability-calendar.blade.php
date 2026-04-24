{{--
    Livewire view: doctor/availability-calendar.
    Interactive FullCalendar view for managing availability windows.
    - Drag/click-to-create opens a "new slot" modal.
    - Click an event → edit/delete slot modal.
    - Events are sourced from Api\CalendarController::availabilities.
--}}
<div>
    <div class="card">
        {{-- Card header: title + actions (generate from recurring, add new). --}}
        <div class="card-header d-flex justify-content-between align-items-center pb-2 mb-1">
            <h5>{{ trans('booking::calendar.availability_calendar') }}</h5>
            <div class="d-flex gap-2">
                <button type="button" wire:click="openGenerateModal" class="btn btn-success">
                    <i class="ti tabler-repeat icon-base me-1"></i>
                    {{ trans('booking::calendar.generate_from_recurring') }}
                </button>
                <button type="button" wire:click="openCreateModalForDate" class="btn btn-primary">
                    <i class="ti tabler-plus icon-base me-1"></i>
                    {{ trans('booking::calendar.add_availability') }}
                </button>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="mb-3">
                <span class="badge bg-success me-2">
                    <i class="ti tabler-repeat me-1"></i>{{ trans('booking::calendar.recurring_slots') }}
                </span>
                <span class="badge bg-primary me-2">
                    <i class="ti tabler-calendar me-1"></i>{{ trans('booking::calendar.manual_slots') }}
                </span>
                <span class="badge bg-secondary">
                    <i class="ti tabler-ban me-1"></i>{{ trans('booking::calendar.inactive_slots') }}
                </span>
            </div>

            <div id="availability-calendar" wire:ignore></div>
        </div>
    </div>

    <!-- Add/Edit Availability Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form wire:submit.prevent="save">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ $editingAvailabilityId ? trans('booking::calendar.edit_availability') : trans('booking::calendar.add_availability') }}
                            </h5>
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">{{ trans('booking::booking.date') }}</label>
                                <input type="date" wire:model="date"
                                       class="form-control @error('date') is-invalid @enderror"
                                       min="{{ now()->format('Y-m-d') }}">
                                @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">{{ trans('booking::booking.start_time') }}</label>
                                    <input type="time" wire:model="start_time"
                                           class="form-control @error('start_time') is-invalid @enderror">
                                    @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">{{ trans('booking::booking.end_time') }}</label>
                                    <input type="time" wire:model="end_time"
                                           class="form-control @error('end_time') is-invalid @enderror">
                                    @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ trans('booking::booking.slot_duration') }}</label>
                                <select wire:model="slot_duration" class="form-select">
                                    <option value="15">15 {{ trans('booking::recurring.minutes') }}</option>
                                    <option value="20">20 {{ trans('booking::recurring.minutes') }}</option>
                                    <option value="30">30 {{ trans('booking::recurring.minutes') }}</option>
                                    <option value="45">45 {{ trans('booking::recurring.minutes') }}</option>
                                    <option value="60">60 {{ trans('booking::recurring.minutes') }}</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ trans('booking::booking.consultation_fee') }}</label>
                                <input type="number" wire:model="consultation_fee" step="0.01" min="0"
                                       class="form-control @error('consultation_fee') is-invalid @enderror">
                                @error('consultation_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-check form-switch">
                                <input type="checkbox" wire:model="is_active" class="form-check-input" id="modal_is_active">
                                <label class="form-check-label" for="modal_is_active">
                                    {{ trans('booking::booking.is_active') }}
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">
                                {{ trans('booking::booking.back') }}
                            </button>
                            <button type="submit" class="btn btn-primary">
                                {{ $editingAvailabilityId ? trans('doctor::doctor.edit') : trans('doctor::doctor.create') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Generate Modal -->
    @if($showGenerateModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form wire:submit.prevent="generateFromRecurring">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ trans('booking::calendar.generate_from_recurring') }}</h5>
                            <button type="button" class="btn-close" wire:click="closeGenerateModal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted">{{ trans('booking::calendar.generate_description') }}</p>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">{{ trans('booking::recurring.fields.start_date') }}</label>
                                    <input type="date" wire:model="generate_start_date"
                                           class="form-control @error('generate_start_date') is-invalid @enderror"
                                           min="{{ now()->format('Y-m-d') }}">
                                    @error('generate_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">{{ trans('booking::recurring.fields.end_date') }}</label>
                                    <input type="date" wire:model="generate_end_date"
                                           class="form-control @error('generate_end_date') is-invalid @enderror">
                                    @error('generate_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="ti tabler-info-circle me-1"></i>
                                {{ trans('booking::calendar.max_days_info') }}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeGenerateModal">
                                {{ trans('booking::booking.back') }}
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="ti tabler-repeat me-1"></i>
                                {{ trans('booking::calendar.generate') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Availability Details Modal -->
    @if($showDetailsModal && $selectedAvailability)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('booking::calendar.availability_details') }}</h5>
                        <button type="button" class="btn-close" wire:click="closeDetailsModal"></button>
                    </div>
                    <div class="modal-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">{{ trans('booking::booking.date') }}</dt>
                            <dd class="col-sm-8">{{ $selectedAvailability['date_formatted'] }}</dd>

                            <dt class="col-sm-4">{{ trans('booking::calendar.time_range') }}</dt>
                            <dd class="col-sm-8">
                                {{ $selectedAvailability['start_time_formatted'] }} - {{ $selectedAvailability['end_time_formatted'] }}
                            </dd>

                            <dt class="col-sm-4">{{ trans('booking::booking.slot_duration') }}</dt>
                            <dd class="col-sm-8">{{ $selectedAvailability['slot_duration'] }} {{ trans('booking::recurring.minutes') }}</dd>

                            <dt class="col-sm-4">{{ trans('booking::booking.consultation_fee') }}</dt>
                            <dd class="col-sm-8">${{ number_format($selectedAvailability['consultation_fee'], 2) }}</dd>

                            <dt class="col-sm-4">{{ trans('booking::calendar.type') }}</dt>
                            <dd class="col-sm-8">
                                @if($selectedAvailability['is_recurring_generated'])
                                    <span class="badge bg-success">{{ trans('booking::calendar.recurring') }}</span>
                                @else
                                    <span class="badge bg-primary">{{ trans('booking::calendar.manual') }}</span>
                                @endif
                            </dd>

                            <dt class="col-sm-4">{{ trans('booking::booking.status') }}</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-{{ $selectedAvailability['is_active'] ? 'success' : 'secondary' }}">
                                    {{ $selectedAvailability['is_active'] ? trans('booking::recurring.active') : trans('booking::recurring.inactive') }}
                                </span>
                            </dd>

                            <dt class="col-sm-4">{{ trans('booking::calendar.available_slots') }}</dt>
                            <dd class="col-sm-8">{{ count($selectedAvailability['available_slots']) }}</dd>
                        </dl>

                        @if($selectedAvailability['has_bookings'])
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="ti tabler-alert-circle me-1"></i>
                                {{ trans('booking::calendar.has_active_bookings') }}
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeDetailsModal">
                            {{ trans('booking::booking.back') }}
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="editAvailability">
                            <i class="ti tabler-edit me-1"></i>
                            {{ trans('doctor::doctor.edit') }}
                        </button>
                        @if(!$selectedAvailability['has_bookings'])
                            <button type="button" class="btn btn-danger" wire:click="deleteAvailability">
                                <i class="ti tabler-trash me-1"></i>
                                {{ trans('doctor::doctor.delete') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initAvailabilityCalendar();
    });

    function initAvailabilityCalendar() {
        const calendarEl = document.getElementById('availability-calendar');
        if (!calendarEl) return;

        const isRtl = document.documentElement.dir === 'rtl';

        const calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, interactionPlugin, timegridPlugin],
            initialView: 'dayGridMonth',
            direction: isRtl ? 'rtl' : 'ltr',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: '{{ trans("booking::booking.today") }}',
                month: '{{ trans("booking::booking.month") }}',
                week: '{{ trans("booking::booking.week") }}',
                day: '{{ trans("booking::booking.day") }}'
            },
            events: @json($events),
            selectable: true,
            selectMirror: true,
            dayMaxEvents: 3,
            moreLinkClick: 'popover',
            height: 'auto',
            contentHeight: 600,
            dateClick: function(info) {
                @this.dispatch('dateClicked', { date: info.dateStr });
            },
            eventClick: function(info) {
                @this.dispatch('eventClicked', { eventId: info.event.id });
            },
            datesSet: function(info) {
                @this.dispatch('dateRangeChanged', {
                    start: info.startStr.split('T')[0],
                    end: info.endStr.split('T')[0]
                });
            },
            eventContent: function(arg) {
                const props = arg.event.extendedProps;
                let html = '<div class="fc-event-main-frame">';
                html += '<div class="fc-event-title-container">';
                html += '<div class="fc-event-title fc-sticky">' + arg.event.title + '</div>';
                if (props.available_slots_count !== undefined) {
                    html += '<small class="d-block">' + props.available_slots_count + ' {{ trans("booking::calendar.slots_available") }}</small>';
                }
                html += '</div></div>';
                return { html: html };
            },
            eventDidMount: function(info) {
                const props = info.event.extendedProps;
                let tooltipText = info.event.title;
                if (props.available_slots_count !== undefined) {
                    tooltipText += ' (' + props.available_slots_count + ' {{ trans("booking::calendar.slots_available") }})';
                }
                info.el.setAttribute('title', tooltipText);
            }
        });

        calendar.render();

        Livewire.on('eventsUpdated', (data) => {
            calendar.removeAllEvents();
            calendar.addEventSource(data.events);
        });
    }
</script>
@endpush
