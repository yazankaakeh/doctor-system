{{--
    Livewire view: conversation-list.
    Middle column of the agent inbox. Lists conversations that match the
    active filters, with search, unread badges, and preview text. Clicking
    a row emits a Livewire event that loads the thread on the right.
--}}
<div class="d-flex flex-column h-100">
    {{-- Search bar — debounced input that filters the list in place. --}}
    <div class="p-4 border-bottom">
        <div class="position-relative">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                class="form-control form-control-sm"
                placeholder="{{ __('messaging::messages.search_conversations') }}"
            >
            @if($search)
                <button
                    wire:click="$set('search', '')"
                    type="button"
                    class="btn btn-sm btn-icon position-absolute end-0 top-50 translate-middle-y"
                >
                    <i class="ki-outline ki-cross fs-6"></i>
                </button>
            @endif
        </div>

        {{-- Filters --}}
        <div class="d-flex gap-2 mt-3">
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
        </div>
    </div>

    {{-- List --}}
    <div class="flex-grow-1 overflow-auto">
        @forelse($conversations as $conversation)
            <div
                wire:click="selectConversation({{ $conversation->id }})"
                class="p-4 border-bottom cursor-pointer hover-bg-light {{ $selectedConversationId === $conversation->id ? 'bg-light-primary' : '' }}"
            >
                <div class="d-flex align-items-start gap-3">
                    <div class="symbol symbol-40px">
                        <span class="symbol-label bg-light-{{ $conversation->channel->type->color() }}">
                            <i class="ki-outline {{ $conversation->channel->type->icon() }} text-{{ $conversation->channel->type->color() }}"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-semibold text-gray-800 text-truncate">
                                {{ $conversation->getDisplayName() }}
                            </span>
                            @if($conversation->unread_count > 0)
                                <span class="badge badge-primary badge-circle">
                                    {{ $conversation->unread_count }}
                                </span>
                            @endif
                        </div>
                        <p class="text-gray-500 text-truncate fs-7 mb-1">
                            {{ $conversation->messages->first()?->getPreviewContent(35) ?? __('messaging::messages.no_messages') }}
                        </p>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-light-{{ $conversation->status->color() }} badge-sm">
                                {{ $conversation->status->label() }}
                            </span>
                            <span class="text-gray-400 fs-8">
                                {{ $conversation->last_message_at?->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-muted">
                <i class="ki-outline ki-sms fs-2x mb-4"></i>
                <p>{{ __('messaging::messages.no_conversations') }}</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($conversations->hasPages())
        <div class="p-3 border-top">
            {{ $conversations->links() }}
        </div>
    @endif
</div>
