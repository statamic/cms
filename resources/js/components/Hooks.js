class Hooks {
    constructor() {
        this.hooks = {};
    }

    on(key, callback) {
        if (this.hooks[key] === undefined) {
            this.hooks[key] = [];
        }

        this.hooks[key].push(callback);
    }

    run(key, payload) {
        let promises = this.hooks[key].map(callback => {
            let result = callback(payload);

            if (result && typeof result.then === 'function') {
                return result;
            } else if (result === false) {
                return Promise.reject();
            }

            return Promise.resolve();
        });

        return new Promise((resolve, reject) => {
            Promise.all(promises).then(values => {
                resolve(values);
            }).catch(error => {
                reject('');
            });
        });
    }
}

export default Hooks;
