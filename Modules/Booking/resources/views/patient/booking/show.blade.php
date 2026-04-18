@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('booking::booking.booking_details'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-success-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <i class="ti tabler-check text-success fs-4"></i>
                                </span>
                                <div>
                                    <h6 class="alert-heading mb-1">{{ trans('booking::booking.booking_created') }}</h6>
                                    <small>{{ trans('booking::booking.booking_created') }}</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h5>{{ trans('booking::booking.booking_details') }}</h5>
                            <span class="badge text-bg-{{ $booking->status->class() }} fs-6">
                                {{ $booking->status->label() }}
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">{{ trans('booking::booking.doctor') }}</h6>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-lg me-3">
                                            <img src="{{ $booking->doctor->getFirstMediaUrl('images') ?: asset('assets/img/avatars/3.png') }}"
                                                 alt="Doctor" class="rounded-circle">
                                        </div>
                                        <div>
                                            <h5 class="mb-0">Dr. {{ $booking->doctor->name }}</h5>
                                            <small class="text-muted">{{ $booking->doctor->medicalSpecialty?->name }}</small>
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
                                        <h6 class="text-muted mb-2">{{ trans('payment::payment.payment_method') }}</h6>
                                        <p>{{ $booking->payment->payment_method->label() }}</p>
                                    @endif
                                </div>
                            </div>

                            @if($booking->notes)
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">{{ trans('booking::booking.notes') }}</h6>
                                    <p>{{ $booking->notes }}</p>
                                </div>
                            @endif

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
                                {{-- Fallback for legacy meeting links --}}
                                <div class="alert alert-success">
                                    <h6 class="alert-heading">{{ trans('booking::booking.meeting_link') }}</h6>
                                    <a href="{{ $booking->meeting_link }}" target="_blank" class="btn btn-success mt-2">
                                        <i class="ti tabler-video me-1"></i>
                                        {{ trans('booking::booking.join_consultation') }}
                                    </a>
                                </div>
                            @endif

                            {{-- Chat with Doctor - Always visible for confirmed bookings --}}
                            @if($booking->isConfirmed() && class_exists('\Modules\Messaging\Services\ConversationService'))
                                <div class="mt-4">
                                    @livewire('booking::booking-conversation', ['booking' => $booking, 'userType' => 'patient'])
                                </div>
                            @endif

                            @if($booking->isPending())
                                <div class="alert alert-warning">
                                    <h6 class="alert-heading">{{ trans('payment::payment.select_payment_method') }}</h6>
                                    <div class="d-flex gap-2 mt-3">
                                        <form action="{{ route('payment.paypal.create', $booking) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ti tabler-brand-paypal me-1"></i>
                                                {{ trans('payment::payment.pay_with_paypal') }}
                                            </button>
                                        </form>
                                        <a href="{{ route('payment.offline.show', $booking) }}" class="btn btn-secondary">
                                            <i class="ti tabler-credit-card me-1"></i>
                                            {{ trans('payment::payment.offline_payment') }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('patient.bookings.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti tabler-arrow-left me-1"></i>
                                    {{ trans('booking::booking.back') }}
                                </a>
                                @if($booking->canBeCancelled())
                                    <form action="{{ route('patient.bookings.cancel', $booking) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger"
                                                onclick="return confirm('{{ trans('booking::booking.cancel_booking') }}?')">
                                            <i class="ti tabler-x me-1"></i>
                                            {{ trans('booking::booking.cancel_booking') }}
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
@endsection

@section('page-script')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            // Show notification
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
