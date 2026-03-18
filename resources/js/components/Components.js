import { ref } from 'vue';
import { nanoid as uniqid } from 'nanoid';
import Component from './Component';

class Components {
    #booted = false;

    constructor() {
        this.queue = {};
        this.components = ref([]);
    }

    boot(app) {
        if (this.#booted) return;

        this.app = app;

        Object.entries(this.queue).forEach(([name, component]) => {
            this.app.component(name, component);
        });

        this.#booted = true;
    }

    register(name, component) {
        if (this.#booted) {
            this.app.component(name, component);
            return;
        }

        this.queue[name] = component;
    }

    append(name, { props }) {
        const id = `appended-${uniqid()}`;
        const component = new Component(id, name, props);
        this.components.value.push(component);
        return component;
    }

    get(id) {
        let appended = this.getAppended(id);
        if (appended) return appended;
    }

    has(name) {
        return this.app.component(name) !== undefined;
    }

    getAppended(id) {
        return this.components.value.find((c) => c.id === id);
    }

    destroy(id) {
        let appended = this.getAppended(id);

        if (appended) {
            const index = this.components.value.indexOf(appended);
            this.components.value.splice(index, 1);
        }
    }
}

export default Components;
