<div class="d-flex flex-column flex-root">
    <div class="page d-flex flex-row flex-column-fluid">
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            {{-- Stats Cards --}}
            <div class="row g-5 g-xl-8 mb-5">
                <div class="col-xl-3">
                    <div class="card card-flush h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-gray-400 fw-semibold fs-6">{{ __('messaging::messages.open_conversations') }}</span>
                                <div class="fs-2hx fw-bold text-gray-800">{{ $stats['open_conversations'] ?? 0 }}</div>
                            </div>
                            <div class="symbol symbol-50px">
                                <span class="symbol-label bg-light-success">
                                    <i class="ki-outline ki-sms fs-2x text-success"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3">
                    <div class="card card-flush h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-gray-400 fw-semibold fs-6">{{ __('messaging::messages.unread_messages') }}</span>
                                <div class="fs-2hx fw-bold text-gray-800">{{ $stats['unread_count'] ?? 0 }}</div>
                            </div>
                            <div class="symbol symbol-50px">
                                <span class="symbol-label bg-light-primary">
                                    <i class="ki-outline ki-notification-status fs-2x text-primary"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3">
                    <div class="card card-flush h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-gray-400 fw-semibold fs-6">{{ __('messaging::messages.total_conversations') }}</span>
                                <div class="fs-2hx fw-bold text-gray-800">{{ $stats['total_conversations'] ?? 0 }}</div>
                            </div>
                            <div class="symbol symbol-50px">
                                <span class="symbol-label bg-light-info">
                                    <i class="ki-outline ki-messages fs-2x text-info"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                @if(auth()->user()->isAdmin())
                    <div class="col-xl-3">
                        <div class="card card-flush h-100">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-gray-400 fw-semibold fs-6">{{ __('messaging::messages.unassigned') }}</span>
                                    <div class="fs-2hx fw-bold text-gray-800">{{ $stats['unassigned'] ?? 0 }}</div>
                                </div>
                                <div class="symbol symbol-50px">
                                    <span class="symbol-label bg-light-warning">
                                        <i class="ki-outline ki-user-tick fs-2x text-warning"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Main Content --}}
            <div class="card card-flush h-lg-100">
                <div class="card-body p-0">
                    <div class="d-flex" style="min-height: 600px;">
                        {{-- Conversation List --}}
                        <div class="w-300px border-end">
                            <livewire:messaging-conversation-list />
                        </div>

                        {{-- Conversation Thread --}}
                        <div class="flex-grow-1 d-flex flex-column">
                            @if($selectedConversationId)
                                <livewire:messaging-conversation-thread :conversation-id="$selectedConversationId" :key="'thread-'.$selectedConversationId" />
                                <livewire:messaging-message-composer :conversation-id="$selectedConversationId" :key="'composer-'.$selectedConversationId" />
                            @else
                                <div class="flex-grow-1 d-flex align-items-center justify-content-center text-muted">
                                    <div class="text-center">
                                        <i class="ki-outline ki-sms fs-5x mb-5"></i>
                                        <p class="fs-4">{{ __('messaging::messages.select_conversation') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Context Panel --}}
                        @if($selectedConversationId)
                            <div class="w-300px border-start">
                                <livewire:messaging-conversation-context :conversation-id="$selectedConversationId" :key="'context-'.$selectedConversationId" />
                                <livewire:messaging-internal-notes :conversation-id="$selectedConversationId" :key="'notes-'.$selectedConversationId" />
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
