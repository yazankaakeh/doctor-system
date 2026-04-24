{{--
    Booking module landing page.
    Used as a placeholder/entry point while the real booking flows live in
    role-specific views (doctor.*, patient.*). Rendered by BookingController@index.
--}}
@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('booking::booking.module'))

@section('content')
    {{-- Page wrapper for the horizontal (top-nav) theme layout. --}}
    <div class="page-wrapper">
        <div class="content">
            {{-- Simple page heading — the localized module name. --}}
            <h1>{{ trans('booking::booking.module') }}</h1>
        </div>
    </div>
@endsection
