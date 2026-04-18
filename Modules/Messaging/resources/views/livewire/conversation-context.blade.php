<div class="p-4 border-bottom">
    <h6 class="fw-semibold mb-4">{{ __('messaging::messages.contact_info') }}</h6>

    @if($conversation)
        <div class="mb-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="symbol symbol-50px">
                    <span class="symbol-label bg-light-{{ $conversation->channel->type->color() }} fs-2">
                        <i class="ki-outline {{ $conversation->channel->type->icon() }} text-{{ $conversation->channel->type->color() }}"></i>
                    </span>
                </div>
                <div>
                    <span class="fw-semibold text-gray-800 d-block">
                        {{ $conversation->getDisplayName() }}
                    </span>
                    <span class="text-gray-500 fs-7">
                        {{ $conversation->participant_identifier }}
                    </span>
                </div>
            </div>
        </div>

        @if($conversableInfo)
            <div class="separator my-4"></div>

            <h6 class="fw-semibold mb-3">{{ __('messaging::messages.linked_record') }}</h6>

            <div class="d-flex flex-column gap-2">
                @foreach($conversableInfo as $field => $value)
                    @if($value)
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-500 fs-7">{{ ucfirst(str_replace('_', ' ', $field)) }}</span>
                            <span class="text-gray-800 fs-7 fw-semibold">{{ $value }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        <div class="separator my-4"></div>

        <h6 class="fw-semibold mb-3">{{ __('messaging::messages.conversation_stats') }}</h6>

        <div class="d-flex flex-column gap-2">
            <div class="d-flex justify-content-between">
                <span class="text-gray-500 fs-7">{{ __('messaging::messages.total_messages') }}</span>
                <span class="text-gray-800 fs-7 fw-semibold">{{ $stats['total_messages'] ?? 0 }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-gray-500 fs-7">{{ __('messaging::messages.inbound') }}</span>
                <span class="text-gray-800 fs-7 fw-semibold">{{ $stats['inbound_messages'] ?? 0 }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-gray-500 fs-7">{{ __('messaging::messages.outbound') }}</span>
                <span class="text-gray-800 fs-7 fw-semibold">{{ $stats['outbound_messages'] ?? 0 }}</span>
            </div>
            @if($stats['first_message_at'] ?? null)
                <div class="d-flex justify-content-between">
                    <span class="text-gray-500 fs-7">{{ __('messaging::messages.first_message') }}</span>
                    <span class="text-gray-800 fs-7 fw-semibold">{{ $stats['first_message_at']->format('M d, Y') }}</span>
                </div>
            @endif
        </div>

        @if($recentActivities && count($recentActivities) > 0)
            <div class="separator my-4"></div>

            <h6 class="fw-semibold mb-3">{{ __('messaging::messages.recent_activities') }}</h6>

            <div class="d-flex flex-column gap-3">
                @foreach($recentActivities as $activity)
                    <div class="d-flex align-items-start gap-2">
                        <div class="w-8px h-8px bg-primary rounded-circle mt-2"></div>
                        <div>
                            <span class="text-gray-800 fs-7 d-block">
                                {{ $activity->description ?? $activity->type ?? 'Activity' }}
                            </span>
                            <span class="text-gray-400 fs-8">
                                {{ $activity->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        <div class="text-center text-muted py-5">
            <i class="ki-outline ki-information fs-2x mb-3"></i>
            <p class="fs-7">{{ __('messaging::messages.select_conversation_to_view') }}</p>
        </div>
    @endif
</div>
