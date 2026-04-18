{{-- Themed Messaging Panel for Dashboard --}}
<div wire:key="messaging-panel-root" @if($isOpen) wire:poll.10s="loadConversations" @endif>
    {{-- Overlay --}}
    <div
        id="messaging-overlay"
        wire:click="close"
        class="tm-msg-overlay {{ $isOpen ? 'show' : '' }}"
    ></div>

    {{-- Panel --}}
    <div id="messaging-panel" class="tm-msg-panel {{ $isOpen ? 'show' : '' }}">
        {{-- Header --}}
        <div class="tm-msg-header">
            <h3 class="tm-msg-title">
                <i class="ri ri-message-2-line"></i>
                {{ __('messaging::messages.messaging') }}
            </h3>
            <div class="d-flex align-items-center gap-2">
                @if($selectedConversationId || $showNewConversation)
                    <button
                        wire:click="backToList"
                        type="button"
                        class="tm-msg-btn tm-msg-btn-icon"
                        title="{{ __('messaging::messages.back') }}"
                    >
                        <i class="ri ri-arrow-left-line"></i>
                    </button>
                @else
                    <button
                        wire:click="showNewConversationForm"
                        type="button"
                        class="tm-msg-btn tm-msg-btn-primary"
                        title="{{ __('messaging::messages.new_message') }}"
                    >
                        <i class="ri ri-add-line"></i>
                        {{ __('messaging::messages.new') }}
                    </button>
                @endif
                <button
                    wire:click="close"
                    type="button"
                    class="tm-msg-btn tm-msg-btn-icon"
                    title="{{ __('Close') }}"
                >
                    <i class="ri ri-close-line"></i>
                </button>
            </div>
        </div>

        @if($showNewConversation)
            {{-- New Conversation Form --}}
            <div class="tm-msg-body">
                {{-- Channel Tabs --}}
                <div class="tm-msg-tabs">
                    @foreach($availableChannels as $channelValue => $channelData)
                        <button
                            wire:click="setChannel('{{ $channelValue }}')"
                            type="button"
                            class="tm-msg-tab tm-msg-tab-{{ $channelValue }} {{ $newConversationChannel === $channelValue ? 'active' : '' }}"
                        >
                            @switch($channelValue)
                                @case('sms')
                                    <i class="ri ri-message-2-line"></i>
                                    @break
                                @case('whatsapp')
                                    <i class="ri ri-whatsapp-line"></i>
                                    @break
                                @case('telegram')
                                    <i class="ri ri-telegram-line"></i>
                                    @break
                                @case('webchat')
                                    <i class="ri ri-chat-smile-2-line"></i>
                                    @break
                                @default
                                    <i class="ri ri-chat-3-line"></i>
                            @endswitch
                            <span>{{ $channelData['name'] }}</span>
                        </button>
                    @endforeach
                </div>
                @error('newConversationChannel')
                    <div class="tm-msg-error">{{ $message }}</div>
                @enderror

                <form wire:submit="startConversation" class="tm-msg-form">
                    @if($newConversationChannel)
                        {{-- User Search --}}
                        <div class="tm-msg-field">
                            <label class="tm-msg-label">{{ __('messaging::messages.recipient') }}</label>
                            <div class="position-relative">
                                @if($selectedUserId)
                                    {{-- Selected User Display --}}
                                    <div class="tm-msg-selected-user">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="tm-msg-avatar tm-msg-avatar-sm">
                                                <i class="ri ri-user-line"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $recipientName }}</div>
                                                <div class="tm-msg-meta">{{ $recipientIdentifier }}</div>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            wire:click="clearSelectedUser"
                                            class="tm-msg-btn tm-msg-btn-icon tm-msg-btn-danger-subtle"
                                        >
                                            <i class="ri ri-close-line"></i>
                                        </button>
                                    </div>
                                @else
                                    {{-- Search Input --}}
                                    <div class="tm-msg-search">
                                        <i class="ri ri-search-line"></i>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.300ms="userSearch"
                                            class="tm-msg-input @error('recipientIdentifier') is-invalid @enderror"
                                            placeholder="{{ __('messaging::messages.search_user_placeholder') }}"
                                            autocomplete="off"
                                        >
                                    </div>

                                    {{-- Search Results Dropdown --}}
                                    @if(count($searchedUsers) > 0)
                                        <div class="tm-msg-dropdown">
                                            @foreach($searchedUsers as $user)
                                                <div
                                                    wire:click="selectUser({{ $user->id }})"
                                                    class="tm-msg-dropdown-item"
                                                >
                                                    <div class="tm-msg-avatar tm-msg-avatar-sm">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $user->name }}</div>
                                                        <div class="tm-msg-meta">
                                                            {{ $user->full_mobile }} @if($user->email) &bull; {{ $user->email }} @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif(strlen($userSearch) >= 2 && count($searchedUsers) === 0)
                                        <div class="tm-msg-dropdown">
                                            <div class="tm-msg-empty-sm">
                                                {{ __('messaging::messages.no_users_found') }}
                                            </div>
                                        </div>
                                    @endif
                                @endif
                                @error('recipientIdentifier')
                                    <div class="tm-msg-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="tm-msg-hint">{{ __('messaging::messages.search_user_hint') }}</div>
                        </div>

                        {{-- Initial Message --}}
                        <div class="tm-msg-field">
                            <label class="tm-msg-label">{{ __('messaging::messages.message') }}</label>
                            <textarea
                                wire:model="initialMessage"
                                class="tm-msg-textarea @error('initialMessage') is-invalid @enderror"
                                rows="4"
                                placeholder="{{ __('messaging::messages.type_message') }}"
                            ></textarea>
                            @error('initialMessage')
                                <div class="tm-msg-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <button
                            type="submit"
                            class="tm-msg-btn tm-msg-btn-primary tm-msg-btn-block"
                            @if($isSending) disabled @endif
                        >
                            @if($isSending)
                                <span class="tm-msg-spinner"></span>
                                {{ __('messaging::messages.sending') }}
                            @else
                                <i class="ri ri-send-plane-line"></i>
                                {{ __('messaging::messages.start_conversation') }}
                            @endif
                        </button>
                    @else
                        <div class="tm-msg-empty">
                            <i class="ri ri-chat-new-line"></i>
                            <p>{{ __('messaging::messages.select_channel_to_start') }}</p>
                        </div>
                    @endif
                </form>
            </div>

        @elseif($selectedConversationId)
            {{-- Conversation View --}}
            <div class="tm-msg-conversation">
                <livewire:messaging-conversation-thread-themed :conversation-id="$selectedConversationId"
                                                               :key="'thread-'.$selectedConversationId"/>
                <livewire:messaging-message-composer-themed :conversation-id="$selectedConversationId"
                                                            :key="'composer-'.$selectedConversationId"/>
            </div>
        @else
            {{-- Channel Filter Tabs --}}
            <div class="tm-msg-tabs tm-msg-tabs-filter">
                <button
                    wire:click="setChannelFilter('')"
                    type="button"
                    class="tm-msg-tab {{ !$channelFilter ? 'active' : '' }}"
                >
                    <span>{{ __('messaging::messages.all') }}</span>
                    @if($conversationCounts['all'] > 0)
                        <span class="tm-msg-badge">{{ $conversationCounts['all'] }}</span>
                    @endif
                </button>
                @foreach($availableChannels as $channelValue => $channelData)
                    <button
                        wire:click="setChannelFilter('{{ $channelValue }}')"
                        type="button"
                        class="tm-msg-tab tm-msg-tab-{{ $channelValue }} {{ $channelFilter === $channelValue ? 'active' : '' }}"
                        title="{{ $channelData['name'] }}"
                    >
                        @switch($channelValue)
                            @case('sms')
                                <i class="ri ri-message-2-line"></i>
                                @break
                            @case('whatsapp')
                                <i class="ri ri-whatsapp-line"></i>
                                @break
                            @case('telegram')
                                <i class="ri ri-telegram-line"></i>
                                @break
                            @case('webchat')
                                <i class="ri ri-chat-smile-2-line"></i>
                                @break
                            @default
                                <i class="ri ri-chat-3-line"></i>
                        @endswitch
                        @if(($conversationCounts[$channelValue] ?? 0) > 0)
                            <span class="tm-msg-badge">{{ $conversationCounts[$channelValue] }}</span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- Search --}}
            <div class="tm-msg-search-bar">
                <div class="tm-msg-search">
                    <i class="ri ri-search-line"></i>
                    <input
                        wire:model.live.debounce.300ms="searchQuery"
                        type="text"
                        class="tm-msg-input"
                        placeholder="{{ __('messaging::messages.search_conversations') }}"
                    >
                    @if($searchQuery)
                        <button
                            wire:click="clearSearch"
                            type="button"
                            class="tm-msg-search-clear"
                        >
                            <i class="ri ri-close-line"></i>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Conversation List --}}
            <div class="tm-msg-list">
                @forelse($conversations as $conversation)
                    <div
                        wire:click="selectConversation({{ $conversation->id }})"
                        class="tm-msg-item {{ $conversation->unread_count > 0 ? 'tm-msg-item-unread' : '' }}"
                    >
                        <div class="tm-msg-item-avatar tm-msg-item-avatar-{{ $conversation->channel->type->value ?? 'default' }}">
                            @switch($conversation->channel->type->value ?? 'default')
                                @case('sms')
                                    <i class="ri ri-message-2-line"></i>
                                    @break
                                @case('whatsapp')
                                    <i class="ri ri-whatsapp-line"></i>
                                    @break
                                @case('telegram')
                                    <i class="ri ri-telegram-line"></i>
                                    @break
                                @case('webchat')
                                    <i class="ri ri-chat-smile-2-line"></i>
                                    @break
                                @default
                                    <i class="ri ri-chat-3-line"></i>
                            @endswitch
                        </div>
                        <div class="tm-msg-item-content">
                            <div class="tm-msg-item-header">
                                <h4 class="tm-msg-item-name">
                                    {{ $conversation->getDisplayName() }}
                                </h4>
                                @if($conversation->unread_count > 0)
                                    <span class="tm-msg-badge tm-msg-badge-primary">
                                        {{ $conversation->unread_count }}
                                    </span>
                                @endif
                            </div>
                            <p class="tm-msg-item-preview">
                                @if($conversation->lastMessage)
                                    @if($conversation->lastMessage->message_type->isMedia())
                                        <i class="ri ri-attachment-2 tm-msg-preview-icon"></i>
                                    @endif
                                    {{ Str::limit($conversation->lastMessage->content ?: __('messaging::messages.attachment'), 50) }}
                                @else
                                    {{ __('messaging::messages.no_messages') }}
                                @endif
                            </p>
                            <div class="tm-msg-item-footer">
                                <span class="tm-msg-status tm-msg-status-{{ $conversation->status->value ?? 'default' }}">
                                    <i class="ri ri-circle-fill"></i>
                                    {{ $conversation->status->label() }}
                                </span>
                                <span class="tm-msg-time">
                                    <i class="ri ri-time-line"></i>
                                    {{ $conversation->last_message_at?->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="tm-msg-empty">
                        <i class="ri ri-chat-off-line"></i>
                        <p>{{ __('messaging::messages.no_conversations') }}</p>
                        <button
                            wire:click="showNewConversationForm"
                            type="button"
                            class="tm-msg-btn tm-msg-btn-primary"
                        >
                            <i class="ri ri-add-line"></i>
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
                window.Echo.private(`agent.${userId}`)
                    .listen('.new-message', (data) => {
                        console.log('New message received:', data);
                        Livewire.dispatch('messaging-new-message');
                    });
            }
        }
    });
</script>
