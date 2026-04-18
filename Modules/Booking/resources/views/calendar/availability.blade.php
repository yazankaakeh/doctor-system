@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('booking::calendar.availability_calendar'))

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/fullcalendar/fullcalendar.scss'], 'build/modules/theme')
    @livewireStyles
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/fullcalendar/fullcalendar.js'], 'build/modules/theme')
    @livewireScripts
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row mb-5">
                <div class="col-12">
                    <livewire:booking::availability-calendar />
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    @stack('page-script')
@endsection
