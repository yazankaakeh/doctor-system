/**
 * Messaging Module - Laravel Echo Integration
 *
 * This file sets up real-time WebSocket listeners for the messaging system.
 * It integrates with Livewire to dispatch events when new messages arrive.
 */

window.initMessagingEcho = function(userId, conversationId = null) {
    if (typeof window.Echo === 'undefined') {
        console.warn('Laravel Echo is not initialized. Real-time messaging disabled.');
        return;
    }

    // Listen to the agent's personal channel for notifications
    if (userId) {
        window.Echo.private(`agent.${userId}`)
            .listen('.new-message', (data) => {
                console.log('New message received on agent channel:', data);

                // Dispatch Livewire events
                if (typeof Livewire !== 'undefined') {
                    Livewire.dispatch('messaging-new-message', { message: data.message, conversation: data.conversation });
                }

                // Play notification sound if available
                if (window.playNotificationSound) {
                    window.playNotificationSound();
                }
            });
    }

    // Listen to a specific conversation channel if viewing one
    if (conversationId) {
        window.Echo.private(`conversation.${conversationId}`)
            .listen('.new-message', (data) => {
                console.log('New message received on conversation channel:', data);

                // Dispatch Livewire events
                if (typeof Livewire !== 'undefined') {
                    Livewire.dispatch('messaging-new-message', { message: data.message, conversation: data.conversation });
                }
            });
    }
};

// Leave a conversation channel when no longer viewing
window.leaveConversationChannel = function(conversationId) {
    if (typeof window.Echo !== 'undefined' && conversationId) {
        window.Echo.leave(`conversation.${conversationId}`);
    }
};

// Initialize on page load if user ID is available
document.addEventListener('DOMContentLoaded', function() {
    const userIdMeta = document.querySelector('meta[name="user-id"]');
    if (userIdMeta) {
        const userId = userIdMeta.content;
        window.initMessagingEcho(userId);
    }
});

// Listen for Livewire navigation to reinitialize
document.addEventListener('livewire:navigated', function() {
    const userIdMeta = document.querySelector('meta[name="user-id"]');
    if (userIdMeta) {
        const userId = userIdMeta.content;
        window.initMessagingEcho(userId);
    }
});
