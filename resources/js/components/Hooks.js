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
