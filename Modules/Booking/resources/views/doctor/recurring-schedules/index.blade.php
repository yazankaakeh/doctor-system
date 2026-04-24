{{--
    Doctor → Recurring schedules page.
    Thin wrapper that hosts the <recurring-schedule-manager> Livewire
    component, where the doctor can CRUD their weekly DoctorRecurringSchedule
    templates and trigger bulk availability generation.
--}}
@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('booking::recurring.title'))

{{-- Livewire assets (manager uses reactive inputs & modals). --}}
@section('vendor-style')
    @livewireStyles
@endsection

@section('vendor-script')
    @livewireScripts
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row mb-5">
                <div class="col-12">
                    {{-- Full CRUD UI lives inside the Livewire component. --}}
                    <livewire:booking::recurring-schedule-manager />
                </div>
            </div>
        </div>
    </div>
@endsection
