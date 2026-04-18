<div wire:poll.10s="loadUnreadCount">
    <button
            wire:click="togglePanel"
            type="button"
            class="btn btn-icon mt-5 btn-sm btn-light-primary position-relative"
            title="{{ __('messaging::messages.messaging') }}"
    >
        <i class="ki-outline ki-sms fs-2"></i>

        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge badge-sm badge-circle badge-danger">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>
</div>
