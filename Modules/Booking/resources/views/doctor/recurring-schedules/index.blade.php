@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('booking::recurring.title'))

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
                    <livewire:booking::recurring-schedule-manager />
                </div>
            </div>
        </div>
    </div>
@endsection
