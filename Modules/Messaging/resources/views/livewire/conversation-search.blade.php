{{--
    Livewire view: conversation-search.
    Stand-alone search input (used by the global messaging panel). The
    300ms debounce avoids a DB round-trip on every keystroke.
--}}
<div class="position-relative">
    <input
        wire:model.live.debounce.300ms="query"
        type="text"
        class="form-control form-control-sm"
        placeholder="{{ __('messaging::messages.search_conversations') }}"
    >
    @if($query)
        <button
            wire:click="clear"
            type="button"
            class="btn btn-sm btn-icon position-absolute end-0 top-50 translate-middle-y"
        >
            <i class="ki-outline ki-cross fs-6"></i>
        </button>
    @endif
</div>
