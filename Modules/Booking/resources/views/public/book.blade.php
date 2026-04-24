{{--
    Public "Book appointment" page.
    Publicly accessible booking landing page rendered in the marketing
    layout. Hosts the public-booking-wizard Livewire flow.
--}}
@php
    // Pull theme config so we can respect colors/dir set by the site admin.
    use Modules\Theme\Helpers\Helpers;
    $configData = Helpers::appClasses();
@endphp

@extends('theme::user.layouts.layoutFront')

@section('title', __('booking::booking.book_appointment'))

@section('vendor-style')
    @vite(['resources/assets/vendor/fonts/fontawesome.scss'], 'build/modules/theme')
@endsection

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/front-page-landing.scss'], 'build/modules/theme')
@endsection

@section('content')
    <section class="section-py bg-body">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">{{ __('booking::booking.book_appointment') }}</h2>
                <p class="text-muted lead">{{ __('booking::booking.book_appointment_subtitle') }}</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    @livewire('booking::public-booking-wizard')
                </div>
            </div>
        </div>
    </section>
@endsection
