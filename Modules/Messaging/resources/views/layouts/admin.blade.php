@extends('theme::admin.layouts.app')

@section('content')
    {{ $slot ?? '' }}
@endsection

@push('styles')
<style>
    /* RTL Support */
    [dir="rtl"] .messaging-panel {
        left: 0;
        right: auto;
        transform: translateX(-100%);
    }
    [dir="rtl"] .messaging-panel.open {
        transform: translateX(0);
    }

    /* Message bubbles */
    .message-bubble {
        max-width: 75%;
        word-wrap: break-word;
    }
    .message-bubble.outbound {
        margin-left: auto;
    }
    [dir="rtl"] .message-bubble.outbound {
        margin-left: 0;
        margin-right: auto;
    }

    /* Scrollable message container */
    .messages-container {
        height: calc(100vh - 300px);
        overflow-y: auto;
    }

    /* Channel badges */
    .channel-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
    }
</style>
@endpush
