class Echo {
    constructor() {
        this.configCallbacks = [];
        this.bootedCallbacks = [];
    }

    config(callback) {
        this.configCallbacks.push(callback);
    }

    booted(callback) {
        this.bootedCallbacks.push(callback);
    }

    async start() {
        const [{ default: LaravelEcho }, { default: Pusher }] = await Promise.all([
            import('laravel-echo'),
            import('pusher-js'),
        ]);

        window.Pusher = Pusher;

        let config = {
            ...Statamic.$config.get('broadcasting.options'),
            csrfToken: Statamic.$config.get('csrfToken'),
            authEndpoint: Statamic.$config.get('broadcasting.endpoint'),
        };

        this.configCallbacks.forEach((callback) => (config = callback(config)));

        this.echo = new LaravelEcho(config);

        this.bootedCallbacks.forEach((callback) => callback(this));
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
].forEach((method) => {
    Echo.prototype[method] = function (...args) {
        return this.echo[method](...args);
    };
});

export default Echo;
