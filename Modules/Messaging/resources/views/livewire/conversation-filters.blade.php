{{--
    Livewire view: conversation-filters.
    Thin filter bar for the agent inbox — exposes channel + status +
    priority selects that live-update the conversation list via wire:model.live.
--}}
<div class="d-flex gap-2">
    {{-- Channel filter dropdown — wire:model.live so changes are instant. --}}
    <select wire:model.live="channelFilter" class="form-select form-select-sm">
        <option value="">{{ __('messaging::messages.all_channels') }}</option>
        @foreach($channelOptions as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>

    <select wire:model.live="statusFilter" class="form-select form-select-sm">
        <option value="">{{ __('messaging::messages.all_statuses') }}</option>
        @foreach($statusOptions as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>

    @if($channelFilter || $statusFilter || $assignedFilter)
        <button
            wire:click="clearFilters"
            type="button"
            class="btn btn-sm btn-light-danger"
        >
            <i class="ki-outline ki-cross fs-6"></i>
        </button>
    @endif
</div>
