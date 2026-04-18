@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('core::video.video_consultation'))

@section('vendor-style')
    @livewireStyles
@endsection

@section('vendor-script')
    @livewireScripts
@endsection

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="container-fluid py-4">
            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ $returnUrl }}">
                            <i class="ti tabler-home me-1"></i>
                            {{ trans('core::video.return_to_dashboard') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">{{ trans('core::video.video_consultation') }}</li>
                </ol>
            </nav>

            {{-- Video Room Component --}}
            <livewire:core::video-room
                :room-name="$roomName"
                :participant-id="(string) $participant->id"
                :participant-name="$participant->name"
                :participant-email="$participant->email"
                :participant-role="$participant->role->value"
                :participant-avatar="$participant->avatar"
                :room-url="$config['roomUrl']"
                :booking-id="$bookingId"
                :on-end-redirect-url="$returnUrl"
            />
        </div>
    </div>
</div>
@endsection

@section('page-script')
    @stack('page-script')
@endsection
