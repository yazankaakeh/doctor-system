<div class="card" @if($conversation) wire:poll.5s="loadMessages" @endif>
    <div class="card-header d-flex justify-content-between align-items-center cursor-pointer"
         wire:click="toggleExpanded">
        <div class="d-flex align-items-center">
            <span class="avatar avatar-sm bg-primary-subtle rounded-circle me-2">
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
                <div class="chat-messages p-3" style="height: 300px; overflow-y: auto;" id="chat-messages-{{ $conversation->id }}">
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
    .chat-messages::-webkit-scrollbar {
        width: 6px;
    }
    .chat-messages::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    .chat-messages::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    .chat-messages::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
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
