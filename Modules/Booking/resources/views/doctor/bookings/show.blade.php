{{--
    Doctor → Booking detail page (Doctor\BookingController@show).
    Displays:
      - Patient summary + contact
      - Appointment date/time/duration
      - Fee + payment status
      - Patient notes (if any)
      - Video consultation CTA (join / copy link) when confirmed
      - Real-time chat (Livewire) between doctor and patient
      - Status-transition actions: complete / no-show
--}}
@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('booking::booking.booking_details'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    {{-- Flash alerts from action controllers. --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            <i class="ti tabler-check me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="ti tabler-alert-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Main content card. --}}
                    <div class="card">
                        {{-- Header: title + colored status badge from BookingStatusEnum. --}}
                        <div class="card-header d-flex justify-content-between">
                            <h5>{{ trans('booking::booking.booking_details') }}</h5>
                            <span class="badge text-bg-{{ $booking->status->class() }} fs-6">
                                {{ $booking->status->label() }}
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">{{ trans('booking::booking.patient') }}</h6>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-lg me-3">
                                            <img src="{{ $booking->patient->getFirstMediaUrl('images') ?: asset('assets/img/avatars/1.png') }}"
                                                 alt="Patient" class="rounded-circle">
                                        </div>
                                        <div>
                                            <h5 class="mb-0">{{ $booking->patient->name }}</h5>
                                            @if($booking->patient->phone)
                                                <small class="text-muted">
                                                    <i class="ti tabler-phone me-1"></i>
                                                    {{ $booking->patient->phone }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">{{ trans('booking::booking.booking_date') }}</h6>
                                    <h5>{{ $booking->booking_date->format('l, F j, Y') }}</h5>
                                    <p class="mb-0">
                                        {{ $booking->start_time->format('H:i') }}
                                        - {{ $booking->end_time->format('H:i') }}
                                        ({{ $booking->duration ?? 0 }} min)
                                    </p>
                                </div>
                            </div>

                            <hr>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">{{ trans('booking::booking.fee') }}</h6>
                                    <h4 class="text-primary">${{ number_format($booking->consultation_fee, 2) }}</h4>
                                </div>
                                <div class="col-md-6">
                                    @if($booking->payment)
                                        <h6 class="text-muted mb-2">{{ trans('payment::payment.payment_status') }}</h6>
                                        <span class="badge text-bg-{{ $booking->payment->status->class() }}">
                                            {{ $booking->payment->status->label() }}
                                        </span>
                                    @else
                                        <h6 class="text-muted mb-2">{{ trans('payment::payment.payment_status') }}</h6>
                                        <span class="badge text-bg-warning">{{ trans('booking::booking.pending') }}</span>
                                    @endif
                                </div>
                            </div>

                            @if($booking->notes)
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">{{ trans('booking::booking.notes') }}</h6>
                                    <div class="alert alert-light">
                                        <p class="mb-0">{{ $booking->notes }}</p>
                                    </div>
                                </div>
                            @endif

                            {{--
                                Video consultation block.
                                Shown only for CONFIRMED bookings. Two variants:
                                  1. Jitsi room name known → route via internal /video/join
                                  2. Only a raw meeting_link → open in new tab
                            --}}
                            @if($booking->isConfirmed() && $booking->hasMeetingRoom())
                                <div class="alert alert-success">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0">
                                            <span class="avatar avatar-md bg-success-subtle rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="ti tabler-video text-success fs-4"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="alert-heading mb-1">{{ trans('core::video.video_consultation') }}</h6>
                                            <small class="text-muted">{{ trans('booking::booking.meeting_link') }}</small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('video.join', ['roomName' => $booking->getMeetingRoomName(), 'booking' => $booking->id]) }}"
                                           class="btn btn-success">
                                            <i class="ti tabler-video me-1"></i>
                                            {{ trans('booking::booking.join_consultation') }}
                                        </a>
                                        <button type="button" class="btn btn-outline-success"
                                                onclick="copyToClipboard('{{ $booking->getMeetingLink() }}')">
                                            <i class="ti tabler-copy me-1"></i>
                                            {{ trans('core::video.copy_link') }}
                                        </button>
                                    </div>
                                </div>
                            @elseif($booking->isConfirmed() && $booking->meeting_link)
                                <div class="alert alert-success">
                                    <h6 class="alert-heading">{{ trans('booking::booking.meeting_link') }}</h6>
                                    <a href="{{ $booking->meeting_link }}" target="_blank" class="btn btn-success mt-2">
                                        <i class="ti tabler-video me-1"></i>
                                        {{ trans('booking::booking.join_consultation') }}
                                    </a>
                                </div>
                            @endif

                            {{--
                                Chat with patient.
                                The booking-conversation Livewire component resolves the
                                morph-linked Conversation (see Booking::conversation()) and
                                renders the in-chat thread between doctor and patient.
                                Guard: only render when the Messaging module is installed.
                            --}}
                            @if($booking->isConfirmed() && class_exists('\Modules\Messaging\Services\ConversationService'))
                                <div class="mt-4">
                                    @livewire('booking::booking-conversation', ['booking' => $booking, 'userType' => 'doctor'])
                                </div>
                            @endif

                            {{-- Action bar: back + status transition forms. --}}
                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('doctor.bookings.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti tabler-arrow-left me-1"></i>
                                    {{ trans('booking::booking.back') }}
                                </a>
                                <div class="d-flex gap-2">
                                    {{-- Complete / No-show: only meaningful for CONFIRMED bookings. --}}
                                    @if($booking->isConfirmed())
                                        <form action="{{ route('doctor.bookings.complete', $booking) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success"
                                                    onclick="return confirm('{{ trans('booking::booking.complete') }}?')">
                                                <i class="ti tabler-check me-1"></i>
                                                {{ trans('booking::booking.complete') }}
                                            </button>
                                        </form>
                                        <form action="{{ route('doctor.bookings.no-show', $booking) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-warning"
                                                    onclick="return confirm('{{ trans('booking::booking.no_show') }}?')">
                                                <i class="ti tabler-user-off me-1"></i>
                                                {{ trans('booking::booking.no_show') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
<script>
    /**
     * Copy the meeting URL to the clipboard and flash a toast confirmation.
     * Falls back to a plain alert when the theme's toast helper isn't loaded.
     */
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            if (typeof Toastify !== 'undefined') {
                Toastify({
                    text: "{{ trans('core::video.link_copied') }}",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    style: { background: "#28a745" }
                }).showToast();
            } else {
                alert("{{ trans('core::video.link_copied') }}");
            }
        }).catch(err => {
            console.error('Failed to copy:', err);
        });
    }
</script>
@endsection
