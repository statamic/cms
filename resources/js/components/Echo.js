import LaravelEcho from 'laravel-echo';
window.Pusher = require('pusher-js');

class Echo {

    constructor() {
        this.bootedCallbacks = [];
    }

    booted(callback) {
        this.bootedCallbacks.push(callback);
    }

    start() {
        const config = {
            broadcaster: 'pusher',
            key: Statamic.$config.get('broadcasting.pusher.key'),
            cluster: Statamic.$config.get('broadcasting.pusher.cluster'),
            encrypted: Statamic.$config.get('broadcasting.pusher.encrypted'),
            csrfToken: Statamic.$config.get('csrfToken'),
            authEndpoint: Statamic.$config.get('broadcasting.endpoint'),
        };

        this.echo = new LaravelEcho(config);

        this.bootedCallbacks.forEach(callback => callback(this));
        this.bootedCallbacks = [];
    }
}

[
    'channel',
    'connect',
    'disconnect',
    'join',
    'leave',
    'leaveChannel',
    'listen',
    'private',
    'socketId',
    'registerInterceptors',
    'registerVueRequestInterceptor',
    'registerAxiosRequestInterceptor',
    'registerjQueryAjaxSetup',
].forEach(method => {
    Echo.prototype[method] = function (...args) {
        return this.echo[method](...args);
    };
});

export default Echo;
