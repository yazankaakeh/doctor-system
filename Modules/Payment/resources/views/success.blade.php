@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('payment::payment.payment_successful'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card text-center">
                        <div class="card-body py-5">
                            <div class="mb-4">
                                <div class="avatar avatar-xl bg-success rounded-circle mx-auto">
                                    <i class="ti tabler-check" style="font-size: 3rem; color: white;"></i>
                                </div>
                            </div>
                            <h2 class="text-success mb-3">{{ trans('payment::payment.payment_successful') }}</h2>
                            <p class="text-muted mb-4">{{ trans('payment::payment.thank_you') }}</p>
                            <p class="mb-4">{{ trans('payment::payment.booking_confirmed_message') }}</p>

                            <div class="alert alert-light text-start mb-4">
                                <div class="row">
                                    <div class="col-6">
                                        <strong>{{ trans('booking::booking.doctor') }}:</strong><br>
                                        Dr. {{ $booking->doctor->name }}
                                    </div>
                                    <div class="col-6">
                                        <strong>{{ trans('booking::booking.booking_date') }}:</strong><br>
                                        {{ $booking->booking_date->format('l, F j, Y') }}
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-6">
                                        <strong>{{ trans('booking::booking.time') }}:</strong><br>
                                        {{ $booking->start_time->format('H:i') }}
                                    </div>
                                    <div class="col-6">
                                        <strong>{{ trans('payment::payment.amount') }}:</strong><br>
                                        ${{ number_format($payment->amount, 2) }}
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('patient.bookings.show', $booking) }}" class="btn btn-primary">
                                {{ trans('booking::booking.booking_details') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
