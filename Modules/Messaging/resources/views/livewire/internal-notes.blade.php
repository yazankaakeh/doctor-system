{{--
    Livewire view: internal-notes.
    Agent-only notes panel for a conversation (not visible to the customer).
    Renders existing notes in reverse-chronological order and exposes a
    compose form at the top. Each note is a ConversationNote row.
--}}
<div class="p-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h6 class="fw-semibold mb-0">{{ __('messaging::messages.internal_notes') }}</h6>
        <button
            wire:click="toggleAddForm"
            type="button"
            class="btn btn-sm btn-icon btn-light-primary"
        >
            <i class="ki-outline {{ $isAdding ? 'ki-cross' : 'ki-plus' }}"></i>
        </button>
    </div>

    @if($isAdding)
        <form wire:submit="addNote" class="mb-4">
            <textarea
                wire:model="newNote"
                rows="3"
                class="form-control form-control-sm mb-2"
                placeholder="{{ __('messaging::messages.add_note_placeholder') }}"
            ></textarea>
            @error('newNote') <span class="text-danger fs-7">{{ $message }}</span> @enderror

            <div class="d-flex justify-content-end gap-2">
                <button
                    wire:click="toggleAddForm"
                    type="button"
                    class="btn btn-sm btn-light"
                >
                    {{ __('messaging::messages.cancel') }}
                </button>
                <button type="submit" class="btn btn-sm btn-primary">
                    {{ __('messaging::messages.save_note') }}
                </button>
            </div>
        </form>
    @endif

    <div class="d-flex flex-column gap-3">
        @forelse($notes as $note)
            <div class="bg-light-warning rounded p-3">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="fw-semibold text-gray-800 fs-7">
                            {{ $note->user?->name ?? 'Unknown' }}
                        </span>
                        <span class="text-gray-400 fs-8 ms-2">
                            {{ $note->created_at->diffForHumans() }}
                        </span>
                    </div>
                    @if($note->isOwnedBy(auth()->user()))
                        <button
                            wire:click="deleteNote({{ $note->id }})"
                            wire:confirm="{{ __('messaging::messages.confirm_delete_note') }}"
                            type="button"
                            class="btn btn-sm btn-icon btn-light-danger"
                        >
                            <i class="ki-outline ki-trash fs-7"></i>
                        </button>
                    @endif
                </div>
                <p class="text-gray-700 fs-7 mb-0 white-space-pre-wrap">{{ $note->content }}</p>
            </div>
        @empty
            <div class="text-center text-muted py-4">
                <i class="ki-outline ki-notepad fs-2x mb-2"></i>
                <p class="fs-7">{{ __('messaging::messages.no_notes') }}</p>
            </div>
        @endforelse
    </div>
</div>
