import Vue from 'vue'

class Panes {
    constructor(instance) {
        this.instance = instance;
    }

    open(vm) {
        this.instance.$root.pane = true;
    }

    close() {
        this.instance.$root.pane = false;
    }
}

Object.defineProperties(Vue.prototype, {
    $panes: {
        get() {
            return new Panes(this);
        }
    }
});
