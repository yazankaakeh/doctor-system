{{--
    Patient → Book appointment page (Patient\BookingController@index).
    Thin wrapper that hosts the <public-booking-wizard> Livewire component.
--}}
@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('booking::booking.book_appointment'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="text-center mb-4">
                <h4 class="fw-bold">{{ trans('booking::booking.book_appointment') }}</h4>
                <p class="text-muted">{{ trans('booking::booking.book_appointment_subtitle') }}</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    @livewire('booking::public-booking-wizard')
                </div>
            </div>
        </div>
    </div>
@endsection
