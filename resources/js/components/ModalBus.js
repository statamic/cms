import Vue from 'vue'

class ModalBus {
    constructor(instance) {
        this.instance = instance;
        this.modals = instance.$root.modals;
    }

    count() {
        return this.modals.length;
    }

    add(name) {
        this.modals.push(name);
    }

    open(name) {
        this.add(name);
        this.instance.$nextTick(() => this.instance.$modal.show(name));
    }

    remove(name) {
        const i = this.modals.indexOf(name);
        this.modals.splice(i, 1);
    }

    close(name) {
        this.remove(name);
        this.instance.$modal.hide(name);
    }
}

Object.defineProperties(Vue.prototype, {
    $modals: {
        get() {
            return new ModalBus(this);
        }
    }
});
