{{--
    Doctor → Bookings dashboard (rendered by Doctor\BookingController@index).

    Features:
      - Toggle between a table view (upcoming bookings, paginated) and a
        FullCalendar view (all bookings for the month).
      - Row actions: view, mark as completed, mark no-show, join the video
        consultation room.
      - A shared "booking details" modal used from the calendar's eventClick.
--}}
@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('booking::booking.bookings'))

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/fullcalendar/fullcalendar.scss'], 'build/modules/theme')
    {{-- Inline styles overriding FullCalendar theme + status color badges. --}}
    <style>
        .view-toggle .btn {
            padding: 0.5rem 1rem;
        }

        .view-toggle .btn.active {
            background-color: var(--bs-primary);
            color: #fff;
            border-color: var(--bs-primary);
        }

        .fc {
            --fc-border-color: var(--bs-border-color);
            --fc-page-bg-color: var(--bs-body-bg);
            --fc-neutral-bg-color: var(--bs-tertiary-bg);
            --fc-today-bg-color: rgba(var(--bs-primary-rgb), 0.08);
        }

        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .fc .fc-button {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
        }

        .fc .fc-button-primary {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }

        .fc .fc-button-primary:hover {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
            opacity: 0.9;
        }

        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }

        .fc-event {
            cursor: pointer;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        .fc-event.status-pending {
            background-color: var(--bs-warning);
            border-color: var(--bs-warning);
        }

        .fc-event.status-confirmed {
            background-color: var(--bs-success);
            border-color: var(--bs-success);
        }

        .fc-event.status-cancelled {
            background-color: var(--bs-danger);
            border-color: var(--bs-danger);
        }

        .fc-event.status-completed {
            background-color: var(--bs-info);
            border-color: var(--bs-info);
        }

        .fc-event.status-no-show {
            background-color: var(--bs-secondary);
            border-color: var(--bs-secondary);
        }

        .fc-daygrid-event-dot {
            display: none;
        }

        .calendar-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 1rem;
            border-top: 1px solid var(--bs-border-color);
            margin-top: 1rem;
        }

        .calendar-legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--bs-body-color);
        }

        .calendar-legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 3px;
        }

        .booking-tooltip {
            position: absolute;
            z-index: 1000;
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 8px;
            padding: 1rem;
            box-shadow: var(--bs-box-shadow);
            min-width: 250px;
        }

        .booking-tooltip-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--bs-border-color);
        }

        .booking-tooltip-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .booking-tooltip-name {
            font-weight: 600;
            color: var(--bs-body-color);
        }

        .booking-tooltip-time {
            font-size: 0.85rem;
            color: var(--bs-secondary-color);
        }

        .booking-tooltip-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card">
                        {{-- Header: title + table/calendar view switcher (see JS at the bottom). --}}
                        <div class="card-header d-flex justify-content-between align-items-center pb-2 mb-1">
                            <h5 class="mb-0">{{ trans('booking::booking.bookings') }}</h5>
                            <div class="view-toggle btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary active" data-view="table">
                                    <i class="ti tabler-list me-1"></i>
                                    {{ trans('booking::booking.table_view') }}
                                </button>
                                <button type="button" class="btn btn-outline-primary" data-view="calendar">
                                    <i class="ti tabler-calendar me-1"></i>
                                    {{ trans('booking::booking.calendar_view') }}
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- ------ Table view (default) ------------------------------ --}}
                            <div id="tableView">
                                <div class="table-responsive text-nowrap">
                                    <table class="table datanew">
                                        <thead>
                                        <tr>
                                            <th>{{ trans('booking::booking.patient') }}</th>
                                            <th>{{ trans('booking::booking.booking_date') }}</th>
                                            <th>{{ trans('booking::booking.time') }}</th>
                                            <th>{{ trans('booking::booking.duration') }}</th>
                                            <th>{{ trans('booking::booking.fee') }}</th>
                                            <th>{{ trans('booking::booking.status') }}</th>
                                            <th>{{ trans('admin.audits.action') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($bookings as $booking)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-2">
                                                            <img
                                                                src="{{ $booking->patient->getFirstMediaUrl('images') ?: asset('assets/img/avatars/3.png') }}"
                                                                alt="Avatar" class="rounded-circle">
                                                        </div>
                                                        {{ $booking->patient->name }}
                                                    </div>
                                                </td>
                                                <td>{{ $booking->booking_date->format('Y-m-d') }}</td>
                                                <td>{{ $booking->start_time->format('H:i') }}
                                                    - {{ $booking->end_time->format('H:i') }}</td>
                                                <td>{{ $booking->duration ?? 0 }} min</td>
                                                <td>${{ number_format($booking->consultation_fee, 2) }}</td>
                                                <td>
                                                    <span class="badge text-bg-{{ $booking->status->class() }}">
                                                        {{ $booking->status->label() }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-text-secondary btn-icon rounded-pill"
                                                                type="button" data-bs-toggle="dropdown">
                                                            <i class="ti tabler-dots-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <a href="{{ route('doctor.bookings.show', $booking) }}"
                                                               class="dropdown-item">
                                                                <i class="ti tabler-eye me-1"></i>
                                                                {{ trans('doctor::doctor.show') }}
                                                            </a>
                                                            @if($booking->isConfirmed())
                                                                <form
                                                                    action="{{ route('doctor.bookings.complete', $booking) }}"
                                                                    method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit"
                                                                            class="dropdown-item text-success">
                                                                        <i class="ti tabler-check me-1"></i>
                                                                        {{ trans('booking::booking.complete') }}
                                                                    </button>
                                                                </form>
                                                                <form
                                                                    action="{{ route('doctor.bookings.no-show', $booking) }}"
                                                                    method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit"
                                                                            class="dropdown-item text-warning">
                                                                        <i class="ti tabler-user-x me-1"></i>
                                                                        {{ trans('booking::booking.no_show') }}
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            {{-- Join button: prefers our internal Jitsi wrapper when a room name exists. --}}
                                                            @if($booking->hasMeetingRoom() && $booking->getMeetingRoomName())
                                                                <a href="{{ route('video.join', ['roomName' => $booking->getMeetingRoomName(), 'booking' => $booking->id]) }}"
                                                                   class="dropdown-item text-primary">
                                                                    <i class="ti tabler-video me-1"></i>
                                                                    {{ trans('booking::booking.join_consultation') }}
                                                                </a>
                                                            @elseif($booking->meeting_link)
                                                                {{-- Legacy fallback: raw meeting_link pointing at the provider --}}
                                                                <a href="{{ $booking->meeting_link }}" target="_blank"
                                                                   class="dropdown-item text-primary">
                                                                    <i class="ti tabler-video me-1"></i>
                                                                    {{ trans('booking::booking.join_consultation') }}
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    {{ $bookings->links() }}
                                </div>
                            </div>

                            {{-- ------ FullCalendar view (populated via JS below) ------- --}}
                            <div id="calendarView" style="display: none;">
                                <div id="bookingsCalendar"></div>
                                <div class="calendar-legend">
                                    <div class="calendar-legend-item">
                                        <div class="calendar-legend-dot"
                                             style="background-color: var(--bs-warning);"></div>
                                        <span>{{ trans('booking::booking.enum.BookingStatusEnum.1') }}</span>
                                    </div>
                                    <div class="calendar-legend-item">
                                        <div class="calendar-legend-dot"
                                             style="background-color: var(--bs-success);"></div>
                                        <span>{{ trans('booking::booking.enum.BookingStatusEnum.2') }}</span>
                                    </div>
                                    <div class="calendar-legend-item">
                                        <div class="calendar-legend-dot"
                                             style="background-color: var(--bs-danger);"></div>
                                        <span>{{ trans('booking::booking.enum.BookingStatusEnum.3') }}</span>
                                    </div>
                                    <div class="calendar-legend-item">
                                        <div class="calendar-legend-dot"
                                             style="background-color: var(--bs-info);"></div>
                                        <span>{{ trans('booking::booking.enum.BookingStatusEnum.4') }}</span>
                                    </div>
                                    <div class="calendar-legend-item">
                                        <div class="calendar-legend-dot"
                                             style="background-color: var(--bs-secondary);"></div>
                                        <span>{{ trans('booking::booking.enum.BookingStatusEnum.5') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--
        ------------------------------------------------------------------
        Booking details modal
        ------------------------------------------------------------------
        Opened programmatically from the calendar's eventClick handler.
        All fields are populated via DOM manipulation from the event's
        extendedProps (see JS at the bottom of this file).
    --}}
    <div class="modal fade" id="bookingDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('booking::booking.booking_details') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <img id="modalPatientAvatar" src="" alt="Patient" class="rounded-circle me-3"
                             style="width: 60px; height: 60px; object-fit: cover;">
                        <div>
                            <h6 id="modalPatientName" class="mb-1"></h6>
                            <span id="modalBookingStatus" class="badge"></span>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm bg-label-primary me-2">
                                    <span class="avatar-initial rounded"><i class="ti tabler-calendar"></i></span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">{{ trans('booking::booking.date') }}</small>
                                    <span id="modalBookingDate" class="fw-medium"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm bg-label-primary me-2">
                                    <span class="avatar-initial rounded"><i class="ti tabler-clock"></i></span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">{{ trans('booking::booking.time') }}</small>
                                    <span id="modalBookingTime" class="fw-medium"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm bg-label-success me-2">
                                    <span class="avatar-initial rounded"><i class="ti tabler-hourglass"></i></span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">{{ trans('booking::booking.duration') }}</small>
                                    <span id="modalBookingDuration" class="fw-medium"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm bg-label-success me-2">
                                    <span class="avatar-initial rounded"><i
                                            class="ti tabler-currency-dollar"></i></span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">{{ trans('booking::booking.fee') }}</small>
                                    <span id="modalBookingFee" class="fw-medium"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="modalBookingNotes" class="mt-3 pt-3 border-top" style="display: none;">
                        <small class="text-muted d-block mb-1">{{ trans('booking::booking.notes') }}</small>
                        <p id="modalNotesContent" class="mb-0"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <a id="modalViewLink" href="#" class="btn btn-primary">
                        <i class="ti tabler-eye me-1"></i>
                        {{ trans('doctor::doctor.show') }}
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ trans('booking::booking.back') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/fullcalendar/fullcalendar.js'], 'build/modules/theme')
@endsection

@section('page-script')
    <script>
        /**
         * Page bootstrap:
         *   1. Wires the table/calendar view toggle buttons.
         *   2. Serialises bookings → FullCalendar events via PHP json.
         *   3. Lazily initializes the calendar the first time it becomes visible.
         *   4. Hooks eventClick to the shared booking-details modal.
         */
        document.addEventListener('DOMContentLoaded', function () {
            // --- View toggle (table ↔ calendar) ----------------------------
            const viewToggleBtns = document.querySelectorAll('.view-toggle .btn');
            const tableView = document.getElementById('tableView');
            const calendarView = document.getElementById('calendarView');
            let calendar = null;

            viewToggleBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    viewToggleBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const view = this.dataset.view;
                    if (view === 'table') {
                        tableView.style.display = 'block';
                        calendarView.style.display = 'none';
                    } else {
                        tableView.style.display = 'none';
                        calendarView.style.display = 'block';
                        if (!calendar) {
                            initCalendar();
                        } else {
                            calendar.updateSize();
                        }
                    }
                });
            });

            // --- Serialize every booking into a FullCalendar event. --------
            // Uses $allBookings (complete list) so navigating through the
            // calendar doesn't require extra HTTP fetches.
            @php
                $bookingEventsData = $allBookings->map(function($booking) {
                    return [
                        'id' => $booking->id,
                        'title' => $booking->patient?->name ?? 'Unknown',
                        'start' => $booking->booking_date->format('Y-m-d') . 'T' . $booking->start_time->format('H:i:s'),
                        'end' => $booking->booking_date->format('Y-m-d') . 'T' . $booking->end_time->format('H:i:s'),
                        'status' => $booking->status->value,
                        'statusLabel' => $booking->status->label(),
                        'statusClass' => $booking->status->class(),
                        'patientAvatar' => $booking->patient?->getFirstMediaUrl('images') ?: asset('assets/img/avatars/3.png'),
                        'duration' => $booking->duration,
                        'fee' => number_format($booking->consultation_fee, 2),
                        'notes' => $booking->notes,
                        'showUrl' => route('doctor.bookings.show', $booking->id),
                    ];
                })->values()->toArray();
            @endphp
            const bookingEvents = @json($bookingEventsData);

            // Map BookingStatusEnum integer values to CSS classes used for event coloring.
            const statusClasses = {
                1: 'status-pending',
                2: 'status-confirmed',
                3: 'status-cancelled',
                4: 'status-completed',
                5: 'status-no-show'
            };

            function initCalendar() {
                const calendarEl = document.getElementById('bookingsCalendar');
                const isRtl = document.documentElement.dir === 'rtl';

                calendar = new Calendar(calendarEl, {
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
                    events: bookingEvents.map(event => ({
                        id: event.id,
                        title: event.title,
                        start: event.start,
                        end: event.end,
                        classNames: [statusClasses[event.status] || 'status-pending'],
                        extendedProps: {
                            status: event.status,
                            statusLabel: event.statusLabel,
                            statusClass: event.statusClass,
                            patientAvatar: event.patientAvatar,
                            duration: event.duration,
                            fee: event.fee,
                            notes: event.notes,
                            showUrl: event.showUrl
                        }
                    })),
                    eventClick: function (info) {
                        const event = info.event;
                        const props = event.extendedProps;

                        // Populate modal
                        document.getElementById('modalPatientAvatar').src = props.patientAvatar;
                        document.getElementById('modalPatientName').textContent = event.title;
                        document.getElementById('modalBookingStatus').textContent = props.statusLabel;
                        document.getElementById('modalBookingStatus').className = 'badge text-bg-' + props.statusClass;
                        document.getElementById('modalBookingDate').textContent = event.start.toLocaleDateString();
                        document.getElementById('modalBookingTime').textContent =
                            event.start.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'}) + ' - ' +
                            event.end.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
                        document.getElementById('modalBookingDuration').textContent = (props.duration || 0) + ' min';
                        document.getElementById('modalBookingFee').textContent = '$' + props.fee;
                        document.getElementById('modalViewLink').href = props.showUrl;

                        if (props.notes) {
                            document.getElementById('modalBookingNotes').style.display = 'block';
                            document.getElementById('modalNotesContent').textContent = props.notes;
                        } else {
                            document.getElementById('modalBookingNotes').style.display = 'none';
                        }

                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));
                        modal.show();
                    },
                    eventDidMount: function (info) {
                        // Add tooltip
                        info.el.title = info.event.title + ' - ' + info.event.extendedProps.statusLabel;
                    },
                    height: 'auto',
                    contentHeight: 600,
                    dayMaxEvents: 3,
                    moreLinkClick: 'popover',
                    nowIndicator: true,
                    editable: false,
                    selectable: false
                });

                calendar.render();
            }
        });
    </script>
@endsection
