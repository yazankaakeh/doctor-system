{{--
    Livewire view: doctor/appointment-calendar.
    Full-page FullCalendar widget for doctors showing all their bookings.
    - Live status filter (wire:model.live) refreshes the calendar events.
    - Events are fed by Api\CalendarController::bookings.
    - Emits Alpine/JS events that the calendar JS consumes to re-fetch.
--}}
<div>
    <div class="card">
        {{-- Card header: title + live status filter dropdown. --}}
        <div class="card-header d-flex justify-content-between align-items-center pb-2 mb-1">
            <h5>{{ trans('booking::calendar.appointments_calendar') }}</h5>
            <div class="d-flex gap-2 align-items-center">
                <label class="form-label mb-0 me-2">{{ trans('booking::calendar.filter_by_status') }}:</label>
                <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width: auto;">
                    @foreach($this->statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="mb-3">
                <span class="badge bg-warning text-dark me-2">{{ trans('booking::booking.pending') }}</span>
                <span class="badge bg-success me-2">{{ trans('booking::booking.confirmed') }}</span>
                <span class="badge bg-danger me-2">{{ trans('booking::booking.cancelled') }}</span>
                <span class="badge bg-info me-2">{{ trans('booking::booking.completed') }}</span>
                <span class="badge bg-secondary">{{ trans('booking::booking.no_show') }}</span>
            </div>

            <div id="appointment-calendar" wire:ignore></div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    @if($showDetailsModal && $selectedBooking)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('booking::calendar.booking_details') }}</h5>
                        <button type="button" class="btn-close" wire:click="closeDetailsModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">{{ trans('booking::calendar.patient_info') }}</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">{{ trans('booking::booking.patient_name') }}</dt>
                                    <dd class="col-sm-8">{{ $selectedBooking['patient_name'] ?? '-' }}</dd>

                                    <dt class="col-sm-4">{{ trans('booking::booking.phone') }}</dt>
                                    <dd class="col-sm-8">{{ $selectedBooking['patient_phone'] ?? '-' }}</dd>

                                    <dt class="col-sm-4">{{ trans('booking::booking.email') }}</dt>
                                    <dd class="col-sm-8">{{ $selectedBooking['patient_email'] ?? '-' }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">{{ trans('booking::calendar.appointment_info') }}</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">{{ trans('booking::booking.date') }}</dt>
                                    <dd class="col-sm-8">{{ $selectedBooking['booking_date_formatted'] }}</dd>

                                    <dt class="col-sm-4">{{ trans('booking::calendar.time') }}</dt>
                                    <dd class="col-sm-8">
                                        {{ $selectedBooking['start_time'] }} - {{ $selectedBooking['end_time'] }}
                                    </dd>

                                    <dt class="col-sm-4">{{ trans('booking::booking.duration') }}</dt>
                                    <dd class="col-sm-8">{{ $selectedBooking['duration'] }} {{ trans('booking::recurring.minutes') }}</dd>

                                    <dt class="col-sm-4">{{ trans('booking::booking.status') }}</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge {{ $selectedBooking['status_class'] }}">
                                            {{ $selectedBooking['status_label'] }}
                                        </span>
                                    </dd>

                                    <dt class="col-sm-4">{{ trans('booking::booking.consultation_fee') }}</dt>
                                    <dd class="col-sm-8">${{ number_format($selectedBooking['consultation_fee'], 2) }}</dd>
                                </dl>
                            </div>
                        </div>

                        @if($selectedBooking['notes'])
                            <div class="mt-3">
                                <h6 class="text-muted">{{ trans('booking::booking.notes') }}</h6>
                                <p class="mb-0">{{ $selectedBooking['notes'] }}</p>
                            </div>
                        @endif

                        @if(!empty($selectedBooking['meeting_room_name']))
                            <div class="mt-3">
                                <h6 class="text-muted">{{ trans('booking::booking.meeting_link') }}</h6>
                                <a href="{{ route('video.join', ['roomName' => $selectedBooking['meeting_room_name'], 'booking' => $selectedBooking['id']]) }}"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="ti tabler-video me-1"></i>
                                    {{ trans('booking::calendar.join_meeting') }}
                                </a>
                            </div>
                        @elseif(!empty($selectedBooking['meeting_link']))
                            {{-- Legacy fallback --}}
                            <div class="mt-3">
                                <h6 class="text-muted">{{ trans('booking::booking.meeting_link') }}</h6>
                                <a href="{{ $selectedBooking['meeting_link'] }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="ti tabler-video me-1"></i>
                                    {{ trans('booking::calendar.join_meeting') }}
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeDetailsModal">
                            {{ trans('booking::booking.back') }}
                        </button>
                        @if($selectedBooking['can_be_completed'])
                            <button type="button" class="btn btn-success" wire:click="completeBooking">
                                <i class="ti tabler-check me-1"></i>
                                {{ trans('booking::calendar.mark_completed') }}
                            </button>
                        @endif
                        @if($selectedBooking['can_be_marked_no_show'])
                            <button type="button" class="btn btn-warning" wire:click="markNoShow">
                                <i class="ti tabler-user-x me-1"></i>
                                {{ trans('booking::calendar.mark_no_show') }}
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
        initAppointmentCalendar();
    });

    function initAppointmentCalendar() {
        const calendarEl = document.getElementById('appointment-calendar');
        if (!calendarEl) return;

        const isRtl = document.documentElement.dir === 'rtl';

        // Status class mapping
        const statusClasses = {
            1: 'status-pending',
            2: 'status-confirmed',
            3: 'status-cancelled',
            4: 'status-completed',
            5: 'status-no-show'
        };

        const calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, interactionPlugin, listPlugin, timegridPlugin],
            initialView: 'dayGridMonth',
            direction: isRtl ? 'rtl' : 'ltr',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            buttonText: {
                today: '{{ trans("booking::booking.today") }}',
                month: '{{ trans("booking::booking.month") }}',
                week: '{{ trans("booking::booking.week") }}',
                day: '{{ trans("booking::booking.day") }}',
                list: '{{ trans("booking::booking.list") }}'
            },
            events: @json($events).map(event => ({
                ...event,
                classNames: [statusClasses[event.status] || 'status-pending']
            })),
            dayMaxEvents: 3,
            moreLinkClick: 'popover',
            nowIndicator: true,
            height: 'auto',
            contentHeight: 600,
            eventClick: function(info) {
                @this.dispatch('eventClicked', { eventId: 'booking_' + info.event.id });
            },
            datesSet: function(info) {
                @this.dispatch('dateRangeChanged', {
                    start: info.startStr.split('T')[0],
                    end: info.endStr.split('T')[0]
                });
            },
            eventContent: function(arg) {
                const props = arg.event.extendedProps;
                const avatar = props.patient_avatar || '{{ asset("assets/img/avatars/3.png") }}';

                let html = '<div class="fc-event-main-frame d-flex align-items-center gap-1">';
                html += '<img src="' + avatar + '" class="rounded-circle" width="20" height="20" alt="" style="object-fit: cover;">';
                html += '<div class="fc-event-title-container flex-grow-1">';
                if (arg.timeText) {
                    html += '<div class="fc-event-time small">' + arg.timeText + '</div>';
                }
                html += '<div class="fc-event-title fc-sticky text-truncate">' + arg.event.title + '</div>';
                html += '</div></div>';
                return { html: html };
            },
            eventDidMount: function(info) {
                const props = info.event.extendedProps;
                let tooltipText = props.patient_name || info.event.title;
                if (props.patient_phone) {
                    tooltipText += ' - ' + props.patient_phone;
                }
                if (props.status_label) {
                    tooltipText += ' (' + props.status_label + ')';
                }
                info.el.setAttribute('title', tooltipText);
            }
        });

        calendar.render();

        Livewire.on('eventsUpdated', (data) => {
            calendar.removeAllEvents();
            const events = data.events.map(event => ({
                ...event,
                classNames: [statusClasses[event.status] || 'status-pending']
            }));
            calendar.addEventSource(events);
        });
    }
</script>

<style>
    .status-pending { background-color: #ffc107 !important; border-color: #e0a800 !important; color: #212529 !important; }
    .status-confirmed { background-color: #28a745 !important; border-color: #1e7e34 !important; }
    .status-cancelled { background-color: #dc3545 !important; border-color: #bd2130 !important; }
    .status-completed { background-color: #17a2b8 !important; border-color: #117a8b !important; }
    .status-no-show { background-color: #6c757d !important; border-color: #545b62 !important; }
</style>
@endpush
