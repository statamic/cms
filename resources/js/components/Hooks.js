class Hooks {
    constructor() {
        this.hooks = {};
    }

    on(key, callback, priority=10) {
        if (this.hooks[key] === undefined) {
            this.hooks[key] = [];
        }

        this.hooks[key].push({callback, priority});
    }

    run(key, payload) {
        return new Promise((resolve, reject) => {
            this.getCallbacks(key)
                .sort((a, b) => b.priority - a.priority)
                .map(hook => this.convertToPromiseCallback(hook.callback, payload))
                .reduce((promise, callback) => {
                    return promise.then(result => callback().then(Array.prototype.concat.bind(result)));
                }, Promise.resolve([]))
                .then(results => resolve(results))
                .catch(error => reject(error));
        });
    }

    getCallbacks(key) {
        return this.hooks[key] || [];
    }

    convertToPromiseCallback(callback, payload) {
        return () => {
            return new Promise((resolve, reject) => {
                callback(resolve, reject, payload);
            });
        };
    }
}

export default Hooks;
