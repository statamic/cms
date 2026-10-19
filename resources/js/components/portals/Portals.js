import Portal from './Portal';
import { reactive, ref } from 'vue';

export default class Portals {
    #disabled = reactive(new Set());

    constructor() {
        this.portals = ref([]);
    }

    all() {
        return this.portals.value;
    }

    // The topmost portal that's actually rendering into its target. Disabled portals
    // (e.g. fullscreen fieldtypes not in fullscreen) render inline, so they don't count.
    top() {
        return this.all().filter((portal) => !this.isDisabled(portal.id)).at(-1);
    }

    create(name, data = {}) {
        let portal = new Portal(this, name, data);

        this.portals.value.push(portal);

        return portal;
    }

    isDisabled(id) {
        return this.#disabled.has(id);
    }

    setDisabled(id, disabled) {
        disabled ? this.#disabled.add(id) : this.#disabled.delete(id);
    }

    destroy(id) {
        const i = this.portals.value.findIndex((portal) => portal.id === id);

        if (i !== -1) this.portals.value.splice(i, 1);

        this.#disabled.delete(id);
    }

    stacks() {
        return this.portals.value.filter((portal) => portal.data?.type === 'stack');
    }
}
