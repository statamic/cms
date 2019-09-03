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
        let promises = this.getCallbacks(key)
            .sort((a, b) => a.priority - b.priority)
            .map(hook => {
                return this.convertToPromise(hook.callback, payload);
            });

        return new Promise((resolve, reject) => {
            Promise.all(promises).then(values => {
                resolve(values);
            }).catch(error => {
                reject('');
            });
        });
    }

    runBeforeAndAfter(callback, key, payload) {
        let beforeHooks = `${key}.before`;
        let afterHooks = `${key}.after`;

        return new Promise((resolve, reject) => {
            this.run(beforeHooks, payload).then(() => {
                this.convertToPromise(callback).then(success => {
                    this.run(afterHooks, payload).then(resolve(success)).catch(error => reject(error));
                }).catch(error => reject(error));
            }).catch(error => reject(error));
        });
    }

    getCallbacks(key) {
        return this.hooks[key] || [];
    }

    convertToPromise(callback, payload) {
        if (typeof callback.then === 'function') {
            return callback;
        }

        return new Promise((resolve, reject) => {
            let returned = callback(resolve, reject, payload);

            if (returned && typeof returned.then === 'function') {
                returned
                    .then(success => resolve(success))
                    .catch(error => reject(error));
            }
        });
    }
}

export default Hooks;
