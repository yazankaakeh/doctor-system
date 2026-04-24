{{--
    Messaging → Admin analytics page.
    Hosts the <messaging-analytics-dashboard> Livewire component which
    renders charts and KPIs (response time, first-response time, conversation
    volume by channel, agent workload…).
--}}
@extends('theme::admin.layouts.app')

@section('content')
    {{-- All analytics rendering lives inside the Livewire component. --}}
    <livewire:messaging-analytics-dashboard />
@endsection
