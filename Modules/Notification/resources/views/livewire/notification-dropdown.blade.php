<div>
    <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-2 me-xl-1" wire:poll.30s="loadNotifications">
        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
           data-bs-auto-close="outside" aria-expanded="false">
            <i class="icon-base ti tabler-bell icon-lg"></i>
            @if($unreadCount > 0)
                <span class="badge rounded-pill bg-danger badge-notifications">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
            @endif
        </a>
        <ul class="dropdown-menu dropdown-menu-end p-0">
            <li class="dropdown-menu-header border-bottom">
                <div class="dropdown-header d-flex align-items-center py-3">
                    <h6 class="mb-0 me-auto">{{ trans('Notifications') }}</h6>
                    @if($unreadCount > 0)
                        <button wire:click="markAllAsRead" class="btn btn-text-secondary rounded-pill btn-icon">
                            <i class="icon-base ti tabler-checks icon-sm"></i>
                        </button>
                    @endif
                </div>
            </li>
            <li class="dropdown-notifications-list scrollable-container">
                <ul class="list-group list-group-flush">
                    @forelse($notifications as $notification)
                        <li class="list-group-item list-group-item-action dropdown-notifications-item {{ $notification['read'] ? '' : 'unread-notification' }}"
                            wire:click="markAsRead({{ $notification['id'] }})">
                            <div class="d-flex gap-2">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                            <i class="icon-base ti tabler-bell icon-sm"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 small">
                                        {{ $notification['data']['title'] ?? 'Notification' }}
                                    </h6>
                                    <small class="mb-1 d-block text-body">
                                        {{ $notification['data']['message'] ?? $notification['data']['body'] ?? '' }}
                                    </small>
                                    <small class="text-muted">{{ $notification['created_at']->diffForHumans() }}</small>
                                </div>
                                @if(!$notification['read'])
                                    <div class="flex-shrink-0 dropdown-notifications-actions">
                                        <span class="badge badge-dot bg-primary"></span>
                                    </div>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-4">
                            <div class="avatar avatar-lg mx-auto mb-2">
                                <span class="avatar-initial rounded-circle bg-label-secondary">
                                    <i class="icon-base ti tabler-bell-off icon-lg"></i>
                                </span>
                            </div>
                            <p class="text-muted mb-0">{{ trans('No notifications') }}</p>
                        </li>
                    @endforelse
                </ul>
            </li>
            @if(count($notifications) > 0)
                <li class="border-top">
                    <div class="d-grid p-4">
                        <a class="btn btn-primary btn-sm d-flex" href="javascript:void(0);">
                            <small class="align-middle">{{ trans('View all notifications') }}</small>
                        </a>
                    </div>
                </li>
            @endif
        </ul>
    </li>

    <style>
        .unread-notification {
            background-color: rgba(var(--bs-primary-rgb), 0.05);
        }

        .dropdown-notifications-list {
            max-height: 400px;
            overflow-y: auto;
        }

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
</div>
