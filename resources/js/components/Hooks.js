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
        let promises = this.get(key)
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
                this.convertToPromise(callback).then(() => {
                    this.run(afterHooks, payload).then(resolve).catch(reject);
                }).catch(reject);
            }).catch(reject);
        });
    }

    get(key) {
        return this.hooks[key] || [];
    }

    convertToPromise(callback, payload) {
        return new Promise((resolve, reject) => {
            return callback(resolve, reject, payload);
        });
    }
}

export default Hooks;
