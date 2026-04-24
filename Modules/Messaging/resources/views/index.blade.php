{{--
    Messaging module default landing page (scaffold).
    Rendered by MessagingController@index. The real messaging UI lives under
    admin/dashboard.blade.php, livewire/agent-dashboard.blade.php, etc.
--}}
<x-messaging::layouts.master>
    {{-- Placeholder content – replaced by role-specific dashboards. --}}
    <h1>Hello World</h1>

    <p>Module: {!! config('messaging.name') !!}</p>
</x-messaging::layouts.master>
