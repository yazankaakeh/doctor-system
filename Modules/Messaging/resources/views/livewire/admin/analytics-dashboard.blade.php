<div>
    {{-- Period Selector --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="fs-2x fw-bold">{{ __('messaging::messages.messaging_analytics') }}</h1>
        <select wire:model.live="period" class="form-select w-auto">
            @foreach($periodOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-5 g-xl-8 mb-5">
        <div class="col-xl-4">
            <div class="card card-flush h-100">
                <div class="card-body">
                    <span class="text-gray-400 fw-semibold fs-6">{{ __('messaging::messages.total_conversations') }}</span>
                    <div class="fs-2hx fw-bold text-gray-800">{{ number_format($stats['total_conversations'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card card-flush h-100">
                <div class="card-body">
                    <span class="text-gray-400 fw-semibold fs-6">{{ __('messaging::messages.total_messages') }}</span>
                    <div class="fs-2hx fw-bold text-gray-800">{{ number_format($stats['total_messages'] ?? 0) }}</div>
                    <div class="fs-7 text-gray-500">
                        <span class="text-success">{{ $stats['inbound_messages'] ?? 0 }} {{ __('messaging::messages.inbound') }}</span>
                        /
                        <span class="text-primary">{{ $stats['outbound_messages'] ?? 0 }} {{ __('messaging::messages.outbound') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card card-flush h-100">
                <div class="card-body">
                    <span class="text-gray-400 fw-semibold fs-6">{{ __('messaging::messages.avg_response_time') }}</span>
                    <div class="fs-2hx fw-bold text-gray-800">{{ $stats['avg_response_time'] ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-8">
        {{-- Messages by Channel --}}
        <div class="col-xl-6">
            <div class="card card-flush h-100">
                <div class="card-header pt-5">
                    <h3 class="card-title">{{ __('messaging::messages.messages_by_channel') }}</h3>
                </div>
                <div class="card-body pt-0">
                    @forelse($messagesByChannel as $channel => $count)
                        @php
                            $channelEnum = \Modules\Messaging\Enums\ChannelTypeEnum::tryFrom($channel);
                            $total = array_sum($messagesByChannel) ?: 1;
                            $percentage = round(($count / $total) * 100, 1);
                        @endphp
                        <div class="d-flex align-items-center mb-4">
                            <div class="symbol symbol-40px me-4">
                                <span class="symbol-label bg-light-{{ $channelEnum?->color() ?? 'primary' }}">
                                    <i class="ki-outline {{ $channelEnum?->icon() ?? 'ki-message-text' }} text-{{ $channelEnum?->color() ?? 'primary' }}"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <span class="text-gray-800 fw-semibold d-block fs-6">
                                    {{ $channelEnum?->label() ?? $channel }}
                                </span>
                                <div class="d-flex align-items-center">
                                    <div class="progress h-6px flex-grow-1 me-3">
                                        <div class="progress-bar bg-{{ $channelEnum?->color() ?? 'primary' }}" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <span class="text-gray-400 fw-semibold fs-7">{{ number_format($count) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            {{ __('messaging::messages.no_data') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Top Agents --}}
        <div class="col-xl-6">
            <div class="card card-flush h-100">
                <div class="card-header pt-5">
                    <h3 class="card-title">{{ __('messaging::messages.top_agents') }}</h3>
                </div>
                <div class="card-body pt-0">
                    @forelse($responseTimesByAgent as $index => $agent)
                        <div class="d-flex align-items-center mb-4">
                            <span class="bullet bullet-vertical h-40px bg-{{ ['primary', 'success', 'info', 'warning', 'danger'][$index % 5] }} me-4"></span>
                            <div class="flex-grow-1">
                                <span class="text-gray-800 fw-semibold d-block fs-6">
                                    {{ $agent->name }}
                                </span>
                                <span class="text-gray-400 fw-semibold fs-7">
                                    {{ number_format($agent->message_count) }} {{ __('messaging::messages.messages_sent') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            {{ __('messaging::messages.no_data') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
