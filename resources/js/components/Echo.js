import LaravelEcho from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

class Echo {

    constructor() {
        this.configCallback = null;
        this.bootedCallbacks = [];
    }

    config(callback) {
        this.configCallback = callback;
    }

    booted(callback) {
        this.bootedCallbacks.push(callback);
    }

    start() {
        let config = {
            broadcaster: 'pusher',
            key: Statamic.$config.get('broadcasting.pusher.key'),
            cluster: Statamic.$config.get('broadcasting.pusher.cluster'),
            encrypted: Statamic.$config.get('broadcasting.pusher.encrypted'),
            csrfToken: Statamic.$config.get('csrfToken'),
            authEndpoint: Statamic.$config.get('broadcasting.endpoint'),
        };

        if (this.configCallback) {
            config = this.configCallback(config);
        }

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
