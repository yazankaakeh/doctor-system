{{--
    Livewire view: booking-conversation.
    Collapsible chat widget embedded inside a booking detail page. Polls the
    backend every 5s while a Conversation exists so new messages appear
    without a full reload. `$userType` switches the labels between
    "Chat with patient" and "Chat with doctor" based on who's viewing.
--}}
<div class="card" @if($conversation) wire:poll.5s="loadMessages" @endif>
    <div class="card-header d-flex justify-content-between align-items-center cursor-pointer"
         wire:click="toggleExpanded">
        <div class="d-flex align-items-center">
            <span class="avatar avatar-sm bg-primary-subtle rounded-circle me-2 d-inline-flex align-items-center justify-content-center">
                <i class="ti tabler-message-circle text-primary"></i>
            </span>
            <h6 class="mb-0">
                @if($userType === 'doctor')
                    {{ trans('booking::booking.chat_with_patient') }}
                @else
                    {{ trans('booking::booking.chat_with_doctor') }}
                @endif
            </h6>
        </div>
        <div class="d-flex align-items-center">
            @if($conversation && $conversation->unread_count > 0)
                <span class="badge bg-danger me-2">{{ $conversation->unread_count }}</span>
            @endif
            <i class="ti tabler-chevron-{{ $isExpanded ? 'up' : 'down' }}"></i>
        </div>
    </div>

    @if($isExpanded)
        <div class="card-body p-0">
            @if(!$conversation)
                <div class="text-center py-5 text-muted">
                    <i class="ti tabler-message-off fs-1 mb-2 d-block"></i>
                    <p class="mb-0">{{ trans('booking::booking.chat_not_available') }}</p>
                </div>
            @else
                {{-- Messages Container --}}
                <div class="chat-messages p-3" id="chat-messages-{{ $conversation->id }}">
                    @if($messages->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="ti tabler-messages fs-2 mb-2 d-block"></i>
                            <p class="mb-0">{{ trans('booking::booking.no_messages_yet') }}</p>
                        </div>
                    @else
                        @foreach($messages as $message)
                            @php
                                $alignment = $this->getMessageAlignment($message);
                                $senderName = $this->getSenderName($message);
                            @endphp

                            @if($alignment === 'center')
                                {{-- System message --}}
                                <div class="text-center mb-3">
                                    <small class="text-muted bg-light rounded-pill px-3 py-1">
                                        {{ $message->content }}
                                    </small>
                                </div>
                            @else
                                <div class="d-flex mb-3 {{ $alignment === 'end' ? 'justify-content-end' : 'justify-content-start' }}">
                                    <div class="chat-bubble {{ $alignment === 'end' ? 'bg-primary text-white' : 'bg-light' }}"
                                         style="max-width: 75%; border-radius: 15px; padding: 10px 15px;">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="{{ $alignment === 'end' ? 'text-white-50' : 'text-muted' }} fw-semibold">
                                                {{ $senderName }}
                                            </small>
                                        </div>
                                        <p class="mb-1" style="word-wrap: break-word;">{{ $message->content }}</p>
                                        <small class="{{ $alignment === 'end' ? 'text-white-50' : 'text-muted' }}">
                                            {{ $message->created_at->format('H:i') }}
                                        </small>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>

                {{-- Message Input --}}
                <div class="border-top p-3">
                    <form wire:submit="sendMessage">
                        <div class="input-group">
                            <input type="text"
                                   class="form-control"
                                   wire:model="newMessage"
                                   placeholder="{{ trans('booking::booking.type_message') }}"
                                   autocomplete="off">
                            <button type="submit"
                                    class="btn btn-primary"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="sendMessage">
                                    <i class="ti tabler-send"></i>
                                </span>
                                <span wire:loading wire:target="sendMessage">
                                    <i class="ti tabler-loader-2 spin"></i>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    @endif
</div>

@push('page-script')
<script>
    // Auto-scroll to bottom when messages change
    document.addEventListener('livewire:updated', function() {
        const container = document.getElementById('chat-messages-{{ $conversation?->id }}');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });

    // Initial scroll to bottom
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('chat-messages-{{ $conversation?->id }}');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
</script>
@endpush

@push('page-style')
<style>
    /* Single scrollable area: only .chat-messages scrolls, its parents do not. */
    .chat-messages {
        height: 300px;
        max-height: 300px;
        overflow-y: auto;
        overflow-x: hidden;
        overscroll-behavior: contain; /* stop scroll chaining to the page */
        scrollbar-gutter: stable;
    }
    /* Make the scrollbar slim and unobtrusive (WebKit / Chromium) */
    .chat-messages::-webkit-scrollbar {
        width: 6px;
    }
    .chat-messages::-webkit-scrollbar-track {
        background: transparent;
    }
    .chat-messages::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    .chat-messages::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    /* Firefox */
    .chat-messages {
        scrollbar-width: thin;
        scrollbar-color: #c1c1c1 transparent;
    }
    /* Kill any accidental scroll on the card/card-body wrappers */
    .card-body:has(> .chat-messages),
    .card:has(.chat-messages) > .card-body {
        overflow: hidden;
        padding: 0;
    }
    /* Center the header avatar icon no matter which theme variant is loaded */
    .card-header .avatar {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }
    .card-header .avatar > i {
        line-height: 1;
    }
    .spin {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>
@endpush
