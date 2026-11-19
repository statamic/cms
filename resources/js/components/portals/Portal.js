import { nanoid as uniqid } from 'nanoid';

export default class Portal {
    constructor(portals, name, data = {}) {
        this.portals = portals;
        this.id = `${name}-${uniqid()}`;
        this.data = data;
    }

    setDisabled(disabled) {
        this.portals.setDisabled(this.id, disabled);
    }

    destroy() {
        this.portals.destroy(this.id);
    }
}
