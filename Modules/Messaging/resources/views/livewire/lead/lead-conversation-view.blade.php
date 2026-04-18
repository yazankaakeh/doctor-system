<div class="flex-grow-1 overflow-auto p-4" style="max-height: 300px;">
    @if($conversation)
        @forelse($messages as $message)
            <div class="d-flex {{ $message->isOutbound() ? 'justify-content-end' : 'justify-content-start' }} mb-3">
                <div class="mw-75 {{ $message->isOutbound() ? 'bg-primary text-white' : 'bg-light' }} rounded p-3">
                    @if($message->isOutbound() && $message->sender)
                        <p class="fs-8 {{ $message->isOutbound() ? 'text-white opacity-75' : 'text-gray-500' }} mb-1">
                            {{ $message->sender->name }}
                        </p>
                    @endif

                    @if($message->message_type->isMedia() && $message->media_url)
                        @if($message->message_type === \Modules\Messaging\Enums\MessageTypeEnum::IMAGE)
                            <img src="{{ $message->media_url }}" alt="Image" class="mw-100 rounded mb-2">
                        @else
                            <a href="{{ $message->media_url }}" target="_blank" class="d-flex align-items-center gap-2 mb-2 {{ $message->isOutbound() ? 'text-white' : '' }}">
                                <i class="ki-outline {{ $message->message_type->icon() }}"></i>
                                <span>{{ $message->metadata['file_name'] ?? __('messaging::messages.download_file') }}</span>
                            </a>
                        @endif
                    @endif

                    <p class="mb-1 white-space-pre-wrap">{{ $message->content }}</p>

                    <div class="d-flex align-items-center justify-content-end gap-2">
                        <span class="fs-8 {{ $message->isOutbound() ? 'text-white opacity-75' : 'text-gray-400' }}">
                            {{ $message->created_at->format('H:i') }}
                        </span>
                        @if($message->isOutbound())
                            <i class="ki-outline {{ $message->status->icon() }} fs-8 {{ $message->status === \Modules\Messaging\Enums\MessageStatusEnum::READ ? 'text-success' : '' }}"></i>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <p class="fs-7">{{ __('messaging::messages.no_messages_yet') }}</p>
            </div>
        @endforelse
    @else
        <div class="text-center text-muted py-5">
            <i class="ki-outline ki-sms fs-2x mb-3"></i>
            <p class="fs-7">{{ __('messaging::messages.conversation_not_found') }}</p>
        </div>
    @endif
</div>
