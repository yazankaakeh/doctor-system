<div class="d-flex gap-2">
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
