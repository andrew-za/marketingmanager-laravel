import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.Pusher = Pusher;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Initialize Laravel Echo for real-time broadcasting
const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1';
const pusherHost = import.meta.env.VITE_PUSHER_HOST;
const pusherPort = import.meta.env.VITE_PUSHER_PORT;
const pusherScheme = import.meta.env.VITE_PUSHER_SCHEME || 'https';

if (pusherKey) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: pusherCluster,
        wsHost: pusherHost || `ws-${pusherCluster}.pusher.com`,
        wsPort: pusherPort || 80,
        wssPort: pusherPort || 443,
        forceTLS: pusherScheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': token?.content || '',
            },
        },
    });
} else {
    console.warn('Pusher key not found. Real-time features will be disabled.');
    window.Echo = null;
}


