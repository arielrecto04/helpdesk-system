import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// Send cookies (session) with requests so broadcasting/auth can use the Laravel session
window.axios.defaults.withCredentials = true;

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to easily build robust real-time web applications.
 */

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    wsPath: import.meta.env.VITE_REVERB_PATH ?? '',
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    }
});

// Echo diagnostics: log connection state, socketId and auth errors
try {
    console.log('[Echo] initialized', { options: window.Echo && window.Echo.options ? window.Echo.options : null });

    const initEchoDiagnostics = () => {
        try {
            const connector = window.Echo && window.Echo.connector ? window.Echo.connector : null;
            console.log('[Echo] connector', connector ? Object.keys(connector) : null);

            if (connector && connector.pusher && connector.pusher.connection) {
                const conn = connector.pusher.connection;

                conn.bind('state_change', (states) => {
                    console.log('[Echo] state_change', states);
                });

                conn.bind('connected', () => {
                    console.log('[Echo] connected', { socketId: window.Echo.socketId ? window.Echo.socketId() : null });
                });

                conn.bind('disconnected', () => {
                    console.warn('[Echo] disconnected');
                });

                conn.bind('error', (err) => {
                    console.error('[Echo] connection error', err);
                });
            }
        } catch (e) {
            console.warn('[Echo] diagnostics failed', e);
        }
    };

    initEchoDiagnostics();

    // Periodically log socket id for debugging
    setInterval(() => {
        try {
            const sid = window.Echo && window.Echo.socketId ? window.Echo.socketId() : undefined;
            console.log('[Echo] socketId (periodic)', sid);
        } catch (e) {}
    }, 5000);

    // Log broadcasting auth failures via axios interceptor
    axios.interceptors.response.use((res) => res, (error) => {
        try {
            const url = error?.config?.url || '';
            if (url.includes('/broadcasting/auth')) {
                console.error('[Echo] broadcasting/auth failed', {
                    url,
                    status: error?.response?.status,
                    data: error?.response?.data,
                    message: error?.message
                });
            }
        } catch (e) {}
        return Promise.reject(error);
    });
} catch (e) {
    console.warn('[Echo] diagnostics initialization error', e);
}