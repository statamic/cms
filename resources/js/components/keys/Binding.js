const mousetrap = require('mousetrap');

export default class Binding {

    constructor(bindings) {
        this.bindings = bindings;
    }

    bind(bindings, callback) {
        if (typeof bindings === 'string') bindings = [bindings];

        bindings.forEach(binding => {
            this.bindings[binding] = this.bindings[binding] || [];
            this.bindings[binding].push(callback);
            this.bindMousetrap(binding, callback);
        });

        this.bound = bindings;

        return this;
    }

    destroy() {
        this.bound.forEach(binding => {
            this.bindings[binding].pop();
            const previous = this.bindings[binding].slice(-1)[0];
            previous ? mousetrap.bind(binding, previous) : mousetrap.unbind(binding);
        });
    }

    stop(callback) {
        mousetrap.prototype.stopCallback = callback;
    }

    bindMousetrap(binding, callback) {
        mousetrap.bind(binding, callback);
    }
}
