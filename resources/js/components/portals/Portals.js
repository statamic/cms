import Portal from './Portal';

export default class Portals {

    constructor(instance) {
        // @todo(jelleroorda): what's this
        this.portals = [] // instance.$root.portals;
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
