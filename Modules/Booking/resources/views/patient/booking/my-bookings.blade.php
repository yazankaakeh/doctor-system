@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('booking::booking.my_bookings'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between pb-2 mb-1">
                            <h5>{{ trans('booking::booking.my_bookings') }}</h5>
                            <a href="{{ route('patient.book.index') }}" class="btn btn-primary">
                                <i class="ti tabler-plus icon-base me-1"></i>
                                {{ trans('booking::booking.book_appointment') }}
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <table class="table datanew">
                                    <thead>
                                    <tr>
                                        <th>{{ trans('booking::booking.doctor') }}</th>
                                        <th>{{ trans('booking::booking.booking_date') }}</th>
                                        <th>{{ trans('booking::booking.time') }}</th>
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
                                                        <img src="{{ $booking->doctor->getFirstMediaUrl('images') ?: asset('assets/img/avatars/3.png') }}"
                                                             alt="Avatar" class="rounded-circle">
                                                    </div>
                                                    <div>
                                                        <strong>{{ $booking->doctor->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $booking->doctor->medicalSpecialty?->name }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $booking->booking_date->format('Y-m-d') }}</td>
                                            <td>{{ $booking->start_time->format('H:i') }}</td>
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
                                                        <a href="{{ route('patient.bookings.show', $booking) }}"
                                                           class="dropdown-item">
                                                            <i class="ti tabler-eye me-1"></i>
                                                            {{ trans('doctor::doctor.show') }}
                                                        </a>

                                                        @if($booking->isPending())
                                                            <a href="{{ route('payment.paypal.create', $booking) }}"
                                                               class="dropdown-item text-primary"
                                                               onclick="event.preventDefault(); document.getElementById('paypal-form-{{ $booking->id }}').submit();">
                                                                <i class="ti tabler-brand-paypal me-1"></i>
                                                                {{ trans('payment::payment.pay_with_paypal') }}
                                                            </a>
                                                            <form id="paypal-form-{{ $booking->id }}"
                                                                  action="{{ route('payment.paypal.create', $booking) }}"
                                                                  method="POST" style="display: none;">
                                                                @csrf
                                                            </form>

                                                            <a href="{{ route('payment.offline.show', $booking) }}"
                                                               class="dropdown-item">
                                                                <i class="ti tabler-credit-card me-1"></i>
                                                                {{ trans('payment::payment.offline_payment') }}
                                                            </a>
                                                        @endif

                                                        @if($booking->canBeCancelled())
                                                            <form action="{{ route('patient.bookings.cancel', $booking) }}"
                                                                  method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item text-danger"
                                                                        onclick="return confirm('{{ trans('booking::booking.cancel_booking') }}?')">
                                                                    <i class="ti tabler-x me-1"></i>
                                                                    {{ trans('booking::booking.cancel_booking') }}
                                                                </button>
                                                            </form>
                                                        @endif

                                                        @if($booking->isConfirmed() && $booking->hasMeetingRoom() && $booking->getMeetingRoomName())
                                                            <a href="{{ route('video.join', ['roomName' => $booking->getMeetingRoomName(), 'booking' => $booking->id]) }}"
                                                               class="dropdown-item text-success">
                                                                <i class="ti tabler-video me-1"></i>
                                                                {{ trans('booking::booking.join_consultation') }}
                                                            </a>
                                                        @elseif($booking->isConfirmed() && $booking->meeting_link)
                                                            {{-- Legacy fallback --}}
                                                            <a href="{{ $booking->meeting_link }}" target="_blank"
                                                               class="dropdown-item text-success">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
