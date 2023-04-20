import Vue from 'vue'
import uniqid from 'uniqid'

class ModalBus {
    constructor(instance) {
        this.instance = instance;
        this.portals = instance.$root.portals;
    }

    count() {
        return this.modals().length;
    }

    add(name) {
        const portal = {
            type: 'modal',
            key: uniqid(),
            name
        };

        this.portals.push(portal);

        return portal;
    }

    open(name) {
        const portal = this.add(name);
        this.instance.$nextTick(() => this.instance.$modal.show(name));
        return portal;
    }

    remove(name) {
        const i = _.findIndex(this.portals, (modal) => modal.name === name);
        this.portals.splice(i, 1);
    }

    close(name) {
        this.remove(name);
        this.instance.$modal.hide(name);
    }

    modals() {
        return this.portals.filter(portal => portal.type === 'stack');
    }
}

Object.defineProperties(Vue.prototype, {
    $modals: {
        get() {
            return new ModalBus(this);
        }
    }
});
