import Vue from 'vue'

class Panes {
    constructor(instance) {
        this.panes = instance.$root.panes;
    }

    open(vm) {
        this.panes.push(vm);

        if (this.panes.length > 1) {
            this.panes.slice(0, -1).forEach(pane => pane.close());
        }
    }

    close(vm) {
        this.panes.splice(this.panes.indexOf(vm), 1);
    }
}

Object.defineProperties(Vue.prototype, {
    $panes: {
        get() {
            return new Panes(this);
        }
    }
});
