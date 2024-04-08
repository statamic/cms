import { Sortable } from '@shopify/draggable'

class Sortables {
    constructor() {
        this.instances = {};
    }

    connect(id, container, options) {
        if (!this.instances[id]) {
            this.instances[id] = new Sortable(container, options);
        } else {
            this.instances[id].addContainer(container);
        }
        return this.instances[id];
    }

    disconnect(id, container) {
        if (this.instances[id]) {
            this.instances[id].removeContainer(container);
            if (this.instances[id].containers.length === 0) {
                this.instances[id].destroy();
                delete this.instances[id];
            }
        }
    }
}

export default Sortables;
