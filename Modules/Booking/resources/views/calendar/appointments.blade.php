{{--
    Doctor appointments calendar page.
    Wraps the Livewire <appointment-calendar> component inside the horizontal
    theme layout. The Livewire component queries the Api\CalendarController
    endpoints to feed FullCalendar events.
--}}
@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('booking::calendar.appointments_calendar'))

{{-- Inject FullCalendar + Livewire styles into the page head. --}}
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/fullcalendar/fullcalendar.scss'], 'build/modules/theme')
    @livewireStyles
@endsection

{{-- Inject FullCalendar + Livewire scripts before </body>. --}}
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/fullcalendar/fullcalendar.js'], 'build/modules/theme')
    @livewireScripts
@endsection

@section('content')
    {{-- Page container following the theme's standard wrapper structure. --}}
    <div class="page-wrapper">
        <div class="content">
            <div class="row mb-5">
                <div class="col-12">
                    {{-- Livewire appointment calendar — handles events, filters, modals. --}}
                    <livewire:booking::appointment-calendar />
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    {{-- Stack for any page-specific JS pushed from sub-components. --}}
    @stack('page-script')
@endsection
