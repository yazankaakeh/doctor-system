{{--
    Livewire view: lead/lead-channel-tabs.
    Channel selector rendered above the Lead chat widget. Lets an agent
    flip between the different channels a lead can be reached on
    (WhatsApp, Email, SMS, …). Each button emits an event that the parent
    lead-messaging component listens for.
--}}
<div class="d-flex gap-2 flex-wrap">
    @foreach($channels as $channelValue => $channelData)
        <button
            wire:click="selectChannel('{{ $channelValue }}')"
            type="button"
            class="btn btn-sm {{ $selectedChannel === $channelValue ? 'btn-'.$channelData['type']->color() : 'btn-light' }} d-flex align-items-center gap-2"
        >
            <i class="ki-outline {{ $channelData['type']->icon() }} fs-6"></i>
            <span>{{ $channelData['type']->label() }}</span>

            @if(isset($channelData['unreadCount']) && $channelData['unreadCount'] > 0)
                <span class="badge badge-light-danger badge-circle ms-1">
                    {{ $channelData['unreadCount'] }}
                </span>
            @endif
        </button>
    @endforeach
</div>
