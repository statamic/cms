import uniqid from 'uniqid';

export default class Portal {
    constructor(portals, name, data = {}) {
        this.portals = portals;
        this.id = `${name}-${uniqid()}`;
        this.data = data;
    }

    destroy() {
        this.portals.destroy(this.id);
    }
}
