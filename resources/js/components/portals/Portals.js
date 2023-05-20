import Vue from 'vue'
import Portal from './Portal';

class Portals {

    constructor(instance) {
        this.portals = instance.$root.portals;
    }

    all() {
        return this.portals;
    }

    create(name, data = {}) {
        let portal = new Portal(this, name, data);

        this.portals.push(portal);

        return portal;
    }

    destroy(id) {
        const i = _.findIndex(this.portals, (portal) => portal.id === id);

        this.portals.splice(i, 1);
    }
}

Object.defineProperties(Vue.prototype, {
    $portals: {
        get() {
            return new Portals(this);
        }
    }
});
