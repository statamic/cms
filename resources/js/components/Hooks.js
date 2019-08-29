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

    get(key) {
        return this.hooks[key] || [];
    }

    run(key, payload) {
        let promises = this.get(key)
            .sort((a, b) => a.priority - b.priority)
            .map(hook => {
                return this.ensureFulfilledPromise(hook.callback(payload));
            });

        return new Promise((resolve, reject) => {
            Promise.all(promises).then(values => {
                resolve(values);
            }).catch(error => {
                reject('');
            });
        });
    }

    runBeforeAndAfter(operation, key, payload) {
        if (typeof operation.then !== 'function') {
            return console.error('First parameter must be a valid promise.');
        }

        let beforeHooks = `${key}.before`;
        let afterHooks = `${key}.after`;

        return new Promise((resolve, reject) => {
            this.run(beforeHooks, payload).then(() => {
                operation.then(() => {
                    this.run(afterHooks, payload).then(() => resolve()).catch(() => reject());
                }).catch(() => reject());
            }).catch(() => reject());
        });
    }

    ensureFulfilledPromise(result) {
        if (result && typeof result.then === 'function') {
            return result;
        } else if (result === false) {
            return Promise.reject();
        }

        return Promise.resolve();
    }
}

export default Hooks;
