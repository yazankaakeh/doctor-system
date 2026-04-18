<div wire:key="messaging-panel-root" @if($isOpen) wire:poll.10s="loadConversations" @endif>
    {{-- Overlay --}}
    <div
            id="messaging-overlay"
            wire:click="close"
            class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 {{ $isOpen ? '' : 'd-none' }}"
            style="z-index: 1040; cursor: pointer;"
    ></div>

    {{-- Panel --}}
    <div
            id="messaging-panel"
            class="position-fixed top-0 end-0 h-100 bg-white shadow-lg d-flex flex-column {{ $isOpen ? '' : 'd-none' }}"
            style="z-index: 1050; width: 400px; max-width: 100vw;"
    >
        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between p-4 border-bottom bg-light">
            <h3 class="fs-5 fw-semibold text-gray-900 mb-0">
                {{ __('messaging::messages.messaging') }}
            </h3>
            <div class="d-flex align-items-center gap-2">
                @if($selectedConversationId || $showNewConversation)
                    <button
                            wire:click="backToList"
                            type="button"
                            class="btn btn-sm btn-icon btn-light"
                            title="{{ __('messaging::messages.back') }}"
                    >
                        <i class="ki-outline ki-arrow-left"></i>
                    </button>
                @else
                    <button
                            wire:click="showNewConversationForm"
                            type="button"
                            class="btn btn-sm btn-primary"
                            title="{{ __('messaging::messages.new_message') }}"
                    >
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ __('messaging::messages.new') }}
                    </button>
                @endif
                <button
                        wire:click="close"
                        type="button"
                        class="btn btn-sm btn-icon btn-light"
                >
                    <i class="ki-outline ki-cross"></i>
                </button>
            </div>
        </div>

        @if($showNewConversation)
            {{-- New Conversation Form --}}
            <div class="flex-grow-1 overflow-auto d-flex flex-column">
                {{-- Channel Tabs --}}
                <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0 fw-semibold" role="tablist">
                    @foreach($availableChannels as $channelValue => $channelData)
                        <li class="nav-item" role="presentation">
                            <a
                                    wire:click.prevent="setChannel('{{ $channelValue }}')"
                                    class="nav-link justify-content-center text-active-gray-800 {{ $newConversationChannel === $channelValue ? 'active' : '' }}"
                                    role="tab"
                                    href="#"
                            >
                                <i class="ki-outline {{ $channelData['icon'] }} fs-4 me-1 text-{{ $channelData['color'] }}"></i>
                                {{ $channelData['name'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                @error('newConversationChannel')
                <div class="text-danger fs-7 px-4 pt-2">{{ $message }}</div>
                @enderror

                <form wire:submit="startConversation" class="flex-grow-1 d-flex flex-column overflow-auto">
                    @if($newConversationChannel)
                        <div class="p-4 flex-grow-1">
                            {{-- User Search --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">{{ __('messaging::messages.recipient') }}</label>
                                <div class="position-relative">
                                    @if($selectedUserId)
                                        {{-- Selected User Display --}}
                                        <div class="d-flex align-items-center justify-content-between p-2 border rounded bg-light-primary">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-user fs-4 text-primary"></i>
                                                <div>
                                                    <div class="fw-semibold text-gray-800">{{ $recipientName }}</div>
                                                    <div class="fs-7 text-gray-500">{{ $recipientIdentifier }}</div>
                                                </div>
                                            </div>
                                            <button
                                                    type="button"
                                                    wire:click="clearSelectedUser"
                                                    class="btn btn-sm btn-icon btn-light-danger"
                                            >
                                                <i class="ki-outline ki-cross fs-6"></i>
                                            </button>
                                        </div>
                                    @else
                                        {{-- Search Input --}}
                                        <div class="position-relative">
                                            <i class="ki-outline ki-magnifier position-absolute top-50 translate-middle-y ms-3 text-gray-400"></i>
                                            <input
                                                    type="text"
                                                    wire:model.live.debounce.300ms="userSearch"
                                                    class="form-control form-control-sm ps-10 @error('recipientIdentifier') is-invalid @enderror"
                                                    placeholder="{{ __('messaging::messages.search_user_placeholder') }}"
                                                    autocomplete="off"
                                            >
                                        </div>

                                        {{-- Search Results Dropdown --}}
                                        @if(count($searchedUsers) > 0)
                                            <div class="position-absolute w-100 mt-1 bg-white border rounded shadow-sm"
                                                 style="z-index: 1060; max-height: 250px; overflow-y: auto;">
                                                @foreach($searchedUsers as $user)
                                                    <div
                                                            wire:click="selectUser({{ $user->id }})"
                                                            class="p-3 border-bottom cursor-pointer bg-hover-light"
                                                    >
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="symbol symbol-35px">
                                                            <span class="symbol-label bg-light-primary text-primary fw-bold">
                                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                                            </span>
                                                            </div>
                                                            <div>
                                                                <div class="fw-semibold text-gray-800">{{ $user->name }}</div>
                                                                <div class="fs-7 text-gray-500">
                                                                    {{ $user->full_mobile }} @if($user->email) &bull; {{ $user->email }} @endif</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif(strlen($userSearch) >= 2 && count($searchedUsers) === 0)
                                            <div class="position-absolute w-100 mt-1 bg-white border rounded shadow-sm p-3 text-center text-muted"
                                                 style="z-index: 1060;">
                                                {{ __('messaging::messages.no_users_found') }}
                                            </div>
                                        @endif
                                    @endif
                                    @error('recipientIdentifier')
                                    <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="fs-7 text-muted mt-1">{{ __('messaging::messages.search_user_hint') }}</div>
                            </div>

                            {{-- Initial Message --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">{{ __('messaging::messages.message') }}</label>
                                <textarea
                                        wire:model="initialMessage"
                                        class="form-control @error('initialMessage') is-invalid @enderror"
                                        rows="4"
                                        placeholder="{{ __('messaging::messages.type_message') }}"
                                ></textarea>
                                @error('initialMessage')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Submit Button --}}
                            <div class="d-grid mt-auto">
                                <button
                                        type="submit"
                                        class="btn btn-primary"
                                        @if($isSending) disabled @endif
                                >
                                    @if($isSending)
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        {{ __('messaging::messages.sending') }}
                                    @else
                                        <i class="ki-outline ki-send me-2"></i>
                                        {{ __('messaging::messages.start_conversation') }}
                                    @endif
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-8 flex-grow-1 d-flex flex-column justify-content-center">
                            <i class="ki-outline ki-sms fs-3x mb-4 d-block"></i>
                            <p class="mb-0">{{ __('messaging::messages.select_channel_to_start') }}</p>
                        </div>
                    @endif
                </form>
            </div>

        @elseif($selectedConversationId)
            {{-- Conversation View --}}
            <div class="flex-grow-1 overflow-hidden d-flex flex-column">
                <livewire:messaging-conversation-thread :conversation-id="$selectedConversationId"
                                                        :key="'thread-'.$selectedConversationId"/>
                <livewire:messaging-message-composer :conversation-id="$selectedConversationId"
                                                     :key="'composer-'.$selectedConversationId"/>
            </div>
        @else
            {{-- Channel Tabs --}}
            <ul class="mx-4 nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0 border-bottom fw-semibold"
                role="tablist">
                <li class="nav-item" role="presentation">
                    <a
                            wire:click.prevent="setChannelFilter('')"
                            class="nav-link justify-content-center text-active-gray-800 {{ !$channelFilter ? 'active' : '' }}"
                            role="tab"
                            href="#"
                    >
                        {{ __('messaging::messages.all') }}
                        @if($conversationCounts['all'] > 0)
                            <span class="badge badge-sm badge-circle badge-light-primary ms-1">{{ $conversationCounts['all'] }}</span>
                        @endif
                    </a>
                </li>
                @foreach($availableChannels as $channelValue => $channelData)
                    <li class="nav-item" role="presentation">
                        <a
                                wire:click.prevent="setChannelFilter('{{ $channelValue }}')"
                                class="nav-link justify-content-center text-active-gray-800 {{ $channelFilter === $channelValue ? 'active' : '' }}"
                                role="tab"
                                href="#"
                                title="{{ $channelData['name'] }}"
                        >
                            <i class="ki-outline {{ $channelData['icon'] }} fs-4 text-{{ $channelData['color'] }}"></i>
                            @if(($conversationCounts[$channelValue] ?? 0) > 0)
                                <span class="badge badge-sm badge-circle badge-light-{{ $channelData['color'] }} ms-1">{{ $conversationCounts[$channelValue] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>

            {{-- Search --}}
            <div class="p-3 border-bottom">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier position-absolute top-50 translate-middle-y ms-3 text-gray-400"></i>
                    <input
                            wire:model.live.debounce.300ms="searchQuery"
                            type="text"
                            class="form-control form-control-sm ps-10"
                            placeholder="{{ __('messaging::messages.search_conversations') }}"
                    >
                    @if($searchQuery)
                        <button
                                wire:click="clearSearch"
                                type="button"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3 text-gray-400 bg-transparent border-0"
                        >
                            <i class="ki-outline ki-cross fs-6"></i>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Conversation List --}}
            <div class="flex-grow-1 overflow-auto">
                @forelse($conversations as $conversation)
                    <div
                            wire:click="selectConversation({{ $conversation->id }})"
                            class="p-4 border-bottom cursor-pointer bg-hover-light"
                    >
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0">
                                <div class="symbol symbol-40px">
                                    <span class="symbol-label bg-light-{{ $conversation->channel->type->color() }}">
                                        <i class="ki-outline {{ $conversation->channel->type->icon() }} text-{{ $conversation->channel->type->color() }}"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h4 class="fs-6 fw-semibold text-gray-900 text-truncate mb-0">
                                        {{ $conversation->getDisplayName() }}
                                    </h4>
                                    @if($conversation->unread_count > 0)
                                        <span class="badge badge-primary badge-circle">
                                            {{ $conversation->unread_count }}
                                        </span>
                                    @endif
                                </div>
                                <p class="fs-7 text-gray-500 text-truncate mb-1">
                                    {{ $conversation->lastMessage?->content ?? __('messaging::messages.no_messages') }}
                                </p>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-light-{{ $conversation->status->color() }}">
                                        {{ $conversation->status->label() }}
                                    </span>
                                    <span class="fs-8 text-gray-400">
                                        {{ $conversation->last_message_at?->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <i class="ki-outline ki-sms fs-2x mb-4 d-block"></i>
                        <p class="mb-3">{{ __('messaging::messages.no_conversations') }}</p>
                        <button
                                wire:click="showNewConversationForm"
                                type="button"
                                class="btn btn-sm btn-primary"
                        >
                            <i class="ki-outline ki-plus me-1"></i>
                            {{ __('messaging::messages.start_new_conversation') }}
                        </button>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>

<script>
    function toggleMessagingPanel() {
        Livewire.dispatch('toggle-messaging-panel');
    }

    function closeMessagingPanel() {
        Livewire.dispatch('close-messaging-panel');
    }

    function openMessagingPanel() {
        Livewire.dispatch('open-messaging-panel');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeMessagingPanel();
        }
    });

    // Laravel Echo real-time integration
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.Echo !== 'undefined') {
            const userId = @json(auth()->id());

            if (userId) {
                // Subscribe to user's agent channel for notifications
                window.Echo.private(`agent.${userId}`)
                    .listen('.new-message', (data) => {
                        console.log('New message received:', data);
                        Livewire.dispatch('messaging-new-message');
                    });
            }
        }
    });
</script>
