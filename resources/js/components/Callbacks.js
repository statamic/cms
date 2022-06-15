export default class Callbacks {

    constructor() {
        this.callbacks = [];
    }

    add(name, callback) {
        this.callbacks[name] = callback;
    }

    call(name, ...args) {
        if (this.callbacks[name]) {
            return this.callbacks[name](...args);
        }
    }

}
