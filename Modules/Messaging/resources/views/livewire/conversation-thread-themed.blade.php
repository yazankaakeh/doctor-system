{{--
    Livewire view: conversation-thread-themed.
    Variant of conversation-thread with the "tm-" theme classes — used
    inside the global messaging panel (dashboard slide-out) so the styling
    matches that surface. Same behaviour as conversation-thread: polls for
    new messages and renders the chat history + composer.
--}}
<div class="tm-thread-container" wire:poll.5s="loadMessages">
    @if($conversation)
        {{-- Conversation Header --}}
        <div class="tm-thread-header">
            <div class="d-flex align-items-center gap-3">
                {{-- Channel Icon Avatar --}}
                <div class="tm-thread-avatar tm-thread-avatar-{{ $conversation->channel->type->value ?? 'default' }}">
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
                <div class="tm-thread-info">
                    <h4 class="tm-thread-name">{{ $conversation->getDisplayName() }}</h4>
                    <div class="tm-thread-meta">
                        <span class="tm-thread-channel">
                            @switch($conversation->channel->type->value ?? 'default')
                                @case('sms')
                                    <i class="ri ri-phone-line"></i>
                                    @break
                                @case('whatsapp')
                                    <i class="ri ri-whatsapp-line"></i>
                                    @break
                                @case('telegram')
                                    <i class="ri ri-telegram-line"></i>
                                    @break
                                @case('webchat')
                                    <i class="ri ri-global-line"></i>
                                    @break
                                @default
                                    <i class="ri ri-chat-1-line"></i>
                            @endswitch
                            {{ $conversation->participant_identifier }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="tm-thread-actions">
                <select
                    wire:change="changeStatus($event.target.value)"
                    class="tm-thread-status-select"
                >
                    @foreach(\Modules\Messaging\Enums\ConversationStatusEnum::cases() as $status)
                        <option value="{{ $status->value }}" {{ $conversation->status === $status ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>

                @if(!$conversation->assigned_user_id)
                    <button
                        wire:click="assignToMe"
                        type="button"
                        class="tm-msg-btn tm-msg-btn-primary"
                    >
                        <i class="ri ri-user-add-line"></i>
                        {{ __('messaging::messages.assign_to_me') }}
                    </button>
                @endif
            </div>
        </div>

        {{-- Messages Container --}}
        <div
            class="tm-thread-messages"
            id="messages-container-{{ $conversationId }}"
            x-data="conversationThreadScroller()"
            x-init="init()"
        >
            {{-- Load More Button --}}
            @if($hasMoreMessages)
                <div class="tm-thread-load-more">
                    <button
                        wire:click="loadMoreMessages"
                        type="button"
                        class="tm-load-more-btn"
                    >
                        <i class="ri ri-arrow-up-line"></i>
                        {{ __('messaging::messages.load_more') }}
                    </button>
                </div>
            @endif

            @php
                $currentUserId = auth()->id();
                $lastDate = null;
            @endphp

            @forelse($messages as $message)
                @php
                    $isMyMessage = $message->sender_id === $currentUserId;
                    $messageDate = $message->created_at->format('Y-m-d');
                    $showDateSeparator = $lastDate !== $messageDate;
                    $lastDate = $messageDate;

                    // Determine sender info
                    if ($isMyMessage) {
                        $senderName = auth()->user()->name ?? 'Me';
                    } elseif ($message->sender) {
                        $senderName = $message->sender->name ?? $conversation->getDisplayName();
                    } else {
                        $senderName = $conversation->participant_name ?? $conversation->getDisplayName();
                    }
                    $senderInitial = strtoupper(substr($senderName, 0, 1));
                @endphp

                {{-- Date Separator --}}
                @if($showDateSeparator)
                    <div class="tm-date-separator">
                        <span>
                            @if($message->created_at->isToday())
                                {{ __('messaging::messages.today') }}
                            @elseif($message->created_at->isYesterday())
                                {{ __('messaging::messages.yesterday') }}
                            @else
                                {{ $message->created_at->format('M d, Y') }}
                            @endif
                        </span>
                    </div>
                @endif

                {{-- Message Bubble --}}
                <div class="tm-message {{ $isMyMessage ? 'tm-message-sent' : 'tm-message-received' }}" data-message-id="{{ $message->id }}">
                    {{-- Avatar --}}
                    <div class="tm-message-avatar">
                        <span>{{ $senderInitial }}</span>
                    </div>

                    <div class="tm-message-wrapper">
                        {{-- Sender name for received --}}
                        @if(!$isMyMessage)
                            <span class="tm-message-sender">{{ $senderName }}</span>
                        @endif

                        {{-- Message Bubble --}}
                        <div class="tm-message-bubble">
                            {{-- Media Content --}}
                            @if($message->message_type->isMedia() && $message->media_url)
                                @if($message->message_type === \Modules\Messaging\Enums\MessageTypeEnum::IMAGE)
                                    <div class="tm-message-media">
                                        <img src="{{ $message->media_url }}" alt="Image" class="tm-message-image">
                                    </div>
                                @elseif($message->message_type === \Modules\Messaging\Enums\MessageTypeEnum::AUDIO)
                                    <div class="tm-message-audio">
                                        <div class="tm-audio-header">
                                            <i class="ri ri-mic-line"></i>
                                            <span>{{ __('messaging::messages.voice_note') }}</span>
                                        </div>
                                        <audio controls>
                                            <source src="{{ $message->media_url }}" type="{{ $message->metadata['mime_type'] ?? 'audio/webm' }}">
                                        </audio>
                                    </div>
                                @elseif($message->message_type === \Modules\Messaging\Enums\MessageTypeEnum::VIDEO)
                                    <div class="tm-message-media">
                                        <video controls class="tm-message-video">
                                            <source src="{{ $message->media_url }}" type="{{ $message->metadata['mime_type'] ?? 'video/mp4' }}">
                                        </video>
                                    </div>
                                @else
                                    <a href="{{ $message->media_url }}" target="_blank" class="tm-message-file">
                                        <div class="tm-file-icon">
                                            <i class="ri ri-file-3-line"></i>
                                        </div>
                                        <div class="tm-file-info">
                                            <span class="tm-file-name">{{ $message->metadata['file_name'] ?? __('messaging::messages.download_file') }}</span>
                                            @if(isset($message->metadata['file_size']))
                                                <span class="tm-file-size">{{ number_format($message->metadata['file_size'] / 1024, 1) }} KB</span>
                                            @endif
                                        </div>
                                        <i class="ri ri-download-2-line"></i>
                                    </a>
                                @endif
                            @endif

                            {{-- Text Content --}}
                            @if($message->content && $message->content !== '[Attachment]')
                                <p class="tm-message-text">{{ $message->content }}</p>
                            @endif

                            {{-- Time & Status --}}
                            <div class="tm-message-footer">
                                <span class="tm-message-time">{{ $message->created_at->format('H:i') }}</span>
                                @if($isMyMessage)
                                    <span class="tm-message-status" title="{{ $message->status->label() }}{{ $message->read_at ? ' - ' . $message->read_at->format('H:i') : ($message->delivered_at ? ' - ' . $message->delivered_at->format('H:i') : '') }}">
                                        @switch($message->status)
                                            @case(\Modules\Messaging\Enums\MessageStatusEnum::READ)
                                                {{-- Blue double check for read --}}
                                                <i class="ri ri-check-double-fill tm-status-read"></i>
                                                @break
                                            @case(\Modules\Messaging\Enums\MessageStatusEnum::DELIVERED)
                                                {{-- Grey double check for delivered --}}
                                                <i class="ri ri-check-double-line tm-status-delivered"></i>
                                                @break
                                            @case(\Modules\Messaging\Enums\MessageStatusEnum::SENT)
                                                {{-- Single check for sent --}}
                                                <i class="ri ri-check-line tm-status-sent"></i>
                                                @break
                                            @case(\Modules\Messaging\Enums\MessageStatusEnum::FAILED)
                                                {{-- Error icon for failed --}}
                                                <i class="ri ri-error-warning-fill tm-status-failed"></i>
                                                @break
                                            @case(\Modules\Messaging\Enums\MessageStatusEnum::QUEUED)
                                            @case(\Modules\Messaging\Enums\MessageStatusEnum::PENDING)
                                                {{-- Clock for pending/queued --}}
                                                <i class="ri ri-time-line tm-status-pending"></i>
                                                @break
                                            @default
                                                <i class="ri ri-time-line tm-status-pending"></i>
                                        @endswitch
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="tm-thread-empty">
                    <div class="tm-empty-icon">
                        <i class="ri ri-chat-3-line"></i>
                    </div>
                    <h5>{{ __('messaging::messages.no_messages_yet') }}</h5>
                    <p>{{ __('messaging::messages.start_conversation_hint') }}</p>
                </div>
            @endforelse
        </div>
    @else
        <div class="tm-thread-no-selection">
            <div class="tm-empty-icon">
                <i class="ri ri-chat-smile-2-line"></i>
            </div>
            <h5>{{ __('messaging::messages.select_conversation') }}</h5>
            <p>{{ __('messaging::messages.choose_conversation_hint') }}</p>
        </div>
    @endif
</div>

@script
<script>
    Alpine.data('conversationThreadScroller', () => ({
        init() {
            const self = this;

            // Scroll to bottom on init
            this.$nextTick(() => {
                self.scrollToBottom();
            });

            // Listen for events
            window.addEventListener('scroll-to-bottom', () => {
                self.scrollToBottom();
            });

            // Watch for Livewire events
            $wire.on('conversation-loaded', () => {
                setTimeout(() => self.scrollToBottom(), 100);
            });

            Livewire.on('message-sent', () => {
                setTimeout(() => self.scrollToBottom(), 150);
            });

            Livewire.on('messaging-new-message', () => {
                setTimeout(() => self.scrollToBottom(), 150);
            });

            // Listen for message status updates (real-time via Echo)
            Livewire.on('message-status-updated', (data) => {
                self.updateMessageStatus(data.message_id, data.status);
            });

            // Initial scroll
            setTimeout(() => self.scrollToBottom(), 300);

            // Watch messages
            $wire.$watch('messages', (value) => {
                if (value && value.length > 0) {
                    setTimeout(() => self.scrollToBottom(), 100);
                }
            });
        },

        scrollToBottom() {
            if (this.$el) {
                this.$el.scrollTop = this.$el.scrollHeight;
            }
        },

        updateMessageStatus(messageId, status) {
            // Find and update the status icon for this message
            const statusEl = document.querySelector(`[data-message-id="${messageId}"] .tm-message-status i`);
            if (statusEl) {
                // Remove old status classes
                statusEl.className = statusEl.className.replace(/tm-status-\w+/g, '');

                // Add new status class and icon
                const iconMap = {
                    'pending': 'ri-time-line tm-status-pending',
                    'queued': 'ri-time-line tm-status-pending',
                    'sent': 'ri-check-line tm-status-sent',
                    'delivered': 'ri-check-double-line tm-status-delivered',
                    'read': 'ri-check-double-fill tm-status-read',
                    'failed': 'ri-error-warning-fill tm-status-failed'
                };

                statusEl.className = 'ri ' + (iconMap[status] || iconMap['pending']);
            }
        }
    }));
</script>
@endscript
