{{--
    Messaging → Admin dashboard page.
    Thin wrapper that renders the <messaging-agent-dashboard> Livewire
    component inside the admin theme layout. The component provides the
    full inbox: conversation list, thread view, composer, and context panel.
--}}
@extends('theme::admin.layouts.app')

@section('content')
    {{-- Full agent inbox is encapsulated in a single Livewire component. --}}
    <livewire:messaging-agent-dashboard />
@endsection
