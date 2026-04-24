{{--
    Livewire view: lead/lead-messaging.
    Parent widget that plugs messaging into the Lead/CRM detail page.
    Composes the channel tabs, the per-channel thread view and a compact
    composer so agents can chat with a lead without leaving the lead page.
--}}
<div class="card card-flush">
    <div class="card-header border-0 pt-5">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold text-gray-900">{{ __('messaging::messages.messaging') }}</span>
        </h3>
    </div>

    <div class="card-body pt-0">
        {{-- Channel Tabs --}}
        <div class="d-flex gap-2 mb-4 flex-wrap">
            @foreach($availableChannels as $channelValue => $channelData)
                <button
                    wire:click="{{ $channelData['hasConversation'] ? "selectChannel('$channelValue')" : "startNewConversation('$channelValue')" }}"
                    type="button"
                    class="btn btn-sm {{ $selectedChannel === $channelValue ? 'btn-'.$channelData['type']->color() : 'btn-light' }} d-flex align-items-center gap-2"
                >
                    <i class="ki-outline {{ $channelData['type']->icon() }} fs-6"></i>
                    <span>{{ $channelData['type']->label() }}</span>

                    @if($channelData['unreadCount'] > 0)
                        <span class="badge badge-light-danger badge-circle ms-1">
                            {{ $channelData['unreadCount'] }}
                        </span>
                    @endif

                    @if(!$channelData['hasConversation'])
                        <i class="ki-outline ki-plus fs-7"></i>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- Conversation View --}}
        @if($selectedConversationId)
            <div class="border rounded" style="height: 400px;">
                <div class="d-flex flex-column h-100">
                    <livewire:messaging-lead-conversation-view :conversation-id="$selectedConversationId" :key="'lead-conv-'.$selectedConversationId" />
                    <livewire:messaging-message-composer :conversation-id="$selectedConversationId" :key="'lead-composer-'.$selectedConversationId" />
                </div>
            </div>
        @else
            <div class="text-center text-muted py-10">
                <i class="ki-outline ki-sms fs-3x mb-4"></i>
                <p>{{ __('messaging::messages.select_channel_to_start') }}</p>
            </div>
        @endif
    </div>
</div>
