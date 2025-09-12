import Binding from './Binding';
import GlobalBinding from './GlobalBinding';

export default class Keys {
    constructor() {
        this.bindings = {};
        this.globals = {};
    }

    bind(bindings, callback) {
        return new Binding(this.bindings).bind(bindings, callback);
    }

    stop(callback) {
        return new Binding(this.bindings).stop(callback);
    }

    bindGlobal(bindings, callback) {
        return new GlobalBinding(this.globals).bind(bindings, callback);
    }
}
