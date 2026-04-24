{{--
    Doctor availability calendar page.
    Shows the doctor's existing availability windows + allows creating/editing
    them visually through the <availability-calendar> Livewire component.
--}}
@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('booking::calendar.availability_calendar'))

{{-- FullCalendar + Livewire styles. --}}
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/fullcalendar/fullcalendar.scss'], 'build/modules/theme')
    @livewireStyles
@endsection

{{-- FullCalendar + Livewire scripts. --}}
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/fullcalendar/fullcalendar.js'], 'build/modules/theme')
    @livewireScripts
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row mb-5">
                <div class="col-12">
                    {{-- Livewire availability calendar — lets doctor drag/drop to edit slots. --}}
                    <livewire:booking::availability-calendar />
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    {{-- Page-specific scripts pushed by child components. --}}
    @stack('page-script')
@endsection
