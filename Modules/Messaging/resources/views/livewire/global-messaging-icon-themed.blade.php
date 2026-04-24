{{--
    Livewire view: global-messaging-icon-themed.
    Themed variant of global-messaging-icon for the admin dashboard header.
    Same behaviour — polls for unread count and opens the messaging panel —
    but uses the dashboard nav-link styling.
--}}
<div wire:poll.10s="loadUnreadCount">
    <a class="nav-link hide-arrow" href="javascript:void(0);" wire:click="togglePanel" title="{{ __('messaging::messages.messaging') }}">
        <i class="icon-base ti tabler-message icon-lg"></i>
        @if($unreadCount > 0)
            <span class="badge rounded-pill bg-danger badge-notifications">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </a>
</div>

<style>
    .badge-notifications {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        font-size: 0.625rem;
        min-width: 1.25rem;
        height: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
