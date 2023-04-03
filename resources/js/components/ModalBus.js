import Vue from 'vue'
import uniqid from 'uniqid'

class ModalBus {
    constructor(instance) {
        this.instance = instance;
    }

    count() {
        return this.modals().length;
    }

    add(name) {
        return this.instance.$portals.create('modal', {
            type: 'modal',
            name
        });
    }

    open(name) {
        const portal = this.add(name);
        this.instance.$nextTick(() => this.instance.$modal.show(name));
        return portal;
    }

    remove(name) {
        const id = _.find(this.instance.$portals.all(), (modal) => modal.data.name === name).id;
        this.instance.$portals.destroy(id);
    }

    close(name) {
        this.remove(name);
        this.instance.$modal.hide(name);
    }

    modals() {
        return this.portals.filter(portal => portal.type === 'modal');
    }
}

Object.defineProperties(Vue.prototype, {
    $modals: {
        get() {
            return new ModalBus(this);
        }
    }
});
