import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Laravel Echo configuration for real-time messaging with Reverb
 */
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Only initialize Echo if Reverb app key is configured
if (import.meta.env.VITE_REVERB_APP_KEY && import.meta.env.VITE_REVERB_APP_KEY !== '${REVERB_APP_KEY}') {
    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
    } catch (error) {
        console.warn('Laravel Echo could not be initialized:', error.message);
        console.warn('Real-time features will be unavailable. Make sure Reverb server is running.');
    }
} else {
    console.warn('Reverb is not configured. Real-time features are disabled.');
    console.warn('To enable real-time features, run: npm run build');
}
