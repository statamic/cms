import Portal from './Portal';
import { ref } from 'vue';
import { findIndex } from 'lodash-es';

export default class Portals {
    constructor() {
        this.portals = ref([]);
    }

    all() {
        return this.portals.value;
    }

    create(name, data = {}) {
        let portal = new Portal(this, name, data);

        this.portals.value.push(portal);

        return portal;
    }

    destroy(id) {
        const i = findIndex(this.portals.value, (portal) => portal.id === id);

        this.portals.value.splice(i, 1);
    }

    stacks() {
        return this.portals.value.filter((portal) => portal.data?.type === 'stack');
    }
}
