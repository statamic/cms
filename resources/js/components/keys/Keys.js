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

    parse(bindings) {
        if (Array.isArray(bindings)) {
            bindings = bindings[0];
        }

        return bindings.toLowerCase().split('+').map(key => {
            switch(key) {
                case "cmd":
                    return "command";
                case "ctrl":
                    return "control";
                case "mod":
                    return "command"; // TODO: handle normalizing 'mod' cross platform
                default:
                    return key;
            }
        });
    }
}
