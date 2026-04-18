<div class="flex-grow-1 d-flex flex-column overflow-hidden" wire:poll.5s="loadMessages">
    @if($conversation)
        {{-- Conversation Header --}}
        <div class="p-3 border-bottom bg-light">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="symbol symbol-45px">
                        <span class="symbol-label bg-light-{{ $conversation->channel->type->color() }} text-{{ $conversation->channel->type->color() }} fs-4 fw-bold">
                            {{ strtoupper(substr($conversation->getDisplayName(), 0, 1)) }}
                        </span>
                    </div>
                    <div>
                        <h4 class="fs-6 fw-bold text-gray-900 mb-0">
                            {{ $conversation->getDisplayName() }}
                        </h4>
                        <div class="d-flex align-items-center gap-2">
                            <i class="ki-outline {{ $conversation->channel->type->icon() }} fs-7 text-{{ $conversation->channel->type->color() }}"></i>
                            <span class="fs-7 text-gray-500">{{ $conversation->participant_identifier }}</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <select
                        wire:change="changeStatus($event.target.value)"
                        class="form-select form-select-sm form-select-solid"
                        style="width: auto;"
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
                            class="btn btn-sm btn-light-primary"
                        >
                            {{ __('messaging::messages.assign_to_me') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Messages Container --}}
        <div
            class="flex-grow-1 overflow-auto p-4"
            id="messages-container-{{ $conversationId }}"
            style="background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);"
            x-data="{
                scrollToBottom() {
                    this.$el.scrollTop = this.$el.scrollHeight;
                }
            }"
            x-init="$nextTick(() => scrollToBottom())"
            @scroll-to-bottom.window="scrollToBottom()"
        >
            <div class="d-flex flex-column gap-4">
                {{-- Load More Button (at top for loading older messages) --}}
                @if($hasMoreMessages)
                    <div class="text-center mb-4">
                        <button
                            wire:click="loadMoreMessages"
                            type="button"
                            class="btn btn-sm btn-light-primary rounded-pill px-4"
                        >
                            <i class="ki-outline ki-arrow-up fs-7 me-1"></i>
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
                    @endphp

                    {{-- Date Separator --}}
                    @if($showDateSeparator)
                        <div class="d-flex align-items-center my-3">
                            <div class="flex-grow-1 border-bottom"></div>
                            <span class="px-3 fs-8 text-gray-500 fw-semibold">
                                @if($message->created_at->isToday())
                                    {{ __('messaging::messages.today') }}
                                @elseif($message->created_at->isYesterday())
                                    {{ __('messaging::messages.yesterday') }}
                                @else
                                    {{ $message->created_at->format('M d, Y') }}
                                @endif
                            </span>
                            <div class="flex-grow-1 border-bottom"></div>
                        </div>
                    @endif

                    {{-- Message Bubble --}}
                    @php
                        // Determine the sender name for avatar and display
                        if ($isMyMessage) {
                            $senderName = auth()->user()->name ?? 'Me';
                        } elseif ($message->sender) {
                            $senderName = $message->sender->name ?? $conversation->getDisplayName();
                        } else {
                            $senderName = $conversation->participant_name ?? $conversation->getDisplayName();
                        }
                        $senderInitial = strtoupper(substr($senderName, 0, 1));
                    @endphp

                    <div class="d-flex {{ $isMyMessage ? 'justify-content-end' : 'justify-content-start' }}">
                        {{-- Avatar for received messages --}}
                        @if(!$isMyMessage)
                            <div class="me-3 mt-auto">
                                <div class="symbol symbol-35px">
                                    <span class="symbol-label bg-light-info text-info fs-7 fw-bold">
                                        {{ $senderInitial }}
                                    </span>
                                </div>
                            </div>
                        @endif

                        <div class="d-flex flex-column {{ $isMyMessage ? 'align-items-end' : 'align-items-start' }}" style="max-width: 75%;">
                            {{-- Sender name for received messages --}}
                            @if(!$isMyMessage)
                                <span class="fs-8 text-gray-600 fw-semibold mb-1 ms-2">
                                    {{ $senderName }}
                                </span>
                            @endif

                            {{-- Message Content --}}
                            <div class="position-relative {{ $isMyMessage ? 'bg-primary' : 'bg-white shadow-sm' }} rounded-3 p-3"
                                 style="{{ $isMyMessage ? 'border-radius: 18px 18px 4px 18px !important;' : 'border-radius: 18px 18px 18px 4px !important; border: 1px solid #e9ecef;' }}">

                                {{-- Media Content --}}
                                @if($message->message_type->isMedia() && $message->media_url)
                                    @if($message->message_type === \Modules\Messaging\Enums\MessageTypeEnum::IMAGE)
                                        <div class="mb-2">
                                            <img src="{{ $message->media_url }}" alt="Image" class="rounded-2" style="max-width: 100%; max-height: 200px; object-fit: cover;">
                                        </div>
                                    @elseif($message->message_type === \Modules\Messaging\Enums\MessageTypeEnum::AUDIO)
                                        {{-- Audio Player for Voice Notes --}}
                                        <div class="mb-2">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="ki-outline ki-microphone fs-5 {{ $isMyMessage ? 'text-white' : 'text-primary' }}"></i>
                                                <span class="fs-8 {{ $isMyMessage ? 'text-white opacity-75' : 'text-gray-600' }}">
                                                    {{ __('messaging::messages.voice_note') }}
                                                </span>
                                            </div>
                                            <audio controls class="w-100" style="height: 36px; max-width: 250px;">
                                                <source src="{{ $message->media_url }}" type="{{ $message->metadata['mime_type'] ?? 'audio/webm' }}">
                                                {{ __('messaging::messages.audio_not_supported') }}
                                            </audio>
                                        </div>
                                    @elseif($message->message_type === \Modules\Messaging\Enums\MessageTypeEnum::VIDEO)
                                        {{-- Video Player --}}
                                        <div class="mb-2">
                                            <video controls class="rounded-2" style="max-width: 100%; max-height: 200px;">
                                                <source src="{{ $message->media_url }}" type="{{ $message->metadata['mime_type'] ?? 'video/mp4' }}">
                                                {{ __('messaging::messages.video_not_supported') }}
                                            </video>
                                        </div>
                                    @else
                                        {{-- Document/File Download --}}
                                        <a href="{{ $message->media_url }}" target="_blank" class="d-flex align-items-center gap-2 mb-2 p-2 rounded {{ $isMyMessage ? 'bg-light-primary text-white' : 'bg-light text-primary' }}">
                                            <i class="ki-outline {{ $message->message_type->icon() }} fs-3"></i>
                                            <div class="d-flex flex-column">
                                                <span class="fs-7 fw-semibold">{{ $message->metadata['file_name'] ?? __('messaging::messages.download_file') }}</span>
                                                @if(isset($message->metadata['file_size']))
                                                    <span class="fs-8 opacity-75">{{ number_format($message->metadata['file_size'] / 1024, 1) }} KB</span>
                                                @endif
                                            </div>
                                        </a>
                                    @endif
                                @endif

                                {{-- Text Content --}}
                                @if($message->content && $message->content !== '[Attachment]')
                                    <p class="mb-0 {{ $isMyMessage ? 'text-white' : 'text-gray-800' }}" style="white-space: pre-wrap; word-break: break-word;">{{ $message->content }}</p>
                                @endif

                                {{-- Time & Status --}}
                                <div class="d-flex align-items-center {{ $isMyMessage ? 'justify-content-end' : 'justify-content-start' }} gap-1 mt-1">
                                    <span class="fs-9 {{ $isMyMessage ? 'text-white opacity-75' : 'text-gray-400' }}">
                                        {{ $message->created_at->format('H:i') }}
                                    </span>
                                    @if($isMyMessage)
                                        @if($message->status === \Modules\Messaging\Enums\MessageStatusEnum::READ)
                                            <i class="ki-outline ki-double-check fs-8 text-white"></i>
                                        @elseif($message->status === \Modules\Messaging\Enums\MessageStatusEnum::DELIVERED)
                                            <i class="ki-outline ki-double-check fs-8 text-white opacity-75"></i>
                                        @elseif($message->status === \Modules\Messaging\Enums\MessageStatusEnum::SENT)
                                            <i class="ki-outline ki-check fs-8 text-white opacity-75"></i>
                                        @elseif($message->status === \Modules\Messaging\Enums\MessageStatusEnum::FAILED)
                                            <i class="ki-outline ki-information fs-8 text-danger"></i>
                                        @else
                                            <i class="ki-outline ki-time fs-8 text-white opacity-50"></i>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Avatar for sent messages --}}
                        @if($isMyMessage)
                            <div class="ms-3 mt-auto">
                                <div class="symbol symbol-35px">
                                    <span class="symbol-label bg-primary text-white fs-7 fw-bold">
                                        {{ $senderInitial }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-10">
                        <i class="ki-outline ki-sms fs-3x text-gray-300 mb-4 d-block"></i>
                        <p class="fs-6 fw-semibold text-gray-500 mb-1">{{ __('messaging::messages.no_messages_yet') }}</p>
                        <p class="fs-7 text-gray-400">{{ __('messaging::messages.start_conversation_hint') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <div class="flex-grow-1 d-flex align-items-center justify-content-center" style="background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);">
            <div class="text-center">
                <i class="ki-outline ki-sms fs-4x text-gray-300 mb-4 d-block"></i>
                <p class="fs-5 fw-semibold text-gray-500 mb-1">{{ __('messaging::messages.select_conversation') }}</p>
                <p class="fs-7 text-gray-400">{{ __('messaging::messages.choose_conversation_hint') }}</p>
            </div>
        </div>
    @endif
</div>

@script
<script>
    // Scroll to bottom function
    function scrollMessagesToBottom() {
        const container = document.getElementById('messages-container-{{ $conversationId }}');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
        // Also dispatch Alpine event
        window.dispatchEvent(new CustomEvent('scroll-to-bottom'));
    }

    // Scroll on conversation loaded
    $wire.on('conversation-loaded', () => {
        setTimeout(scrollMessagesToBottom, 100);
    });

    // Scroll when message is sent
    Livewire.on('message-sent', () => {
        setTimeout(scrollMessagesToBottom, 150);
    });

    // Scroll on new incoming message
    Livewire.on('messaging-new-message', () => {
        setTimeout(scrollMessagesToBottom, 150);
    });

    // Scroll on component init
    setTimeout(scrollMessagesToBottom, 300);

    // Watch for messages property changes and scroll
    $wire.$watch('messages', (value) => {
        if (value && value.length > 0) {
            setTimeout(scrollMessagesToBottom, 100);
        }
    });
</script>
@endscript
