import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: false,
    wsHost: window.location.hostname,
    wsPort: 6001,
    wssPort: 6001,
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
    // authEndpoint: '/broadcasting/auth',
    // auth: {
    //     headers: {
    //         'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
    //     }
    // }
});

export default echo;
