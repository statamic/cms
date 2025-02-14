import { ref } from 'vue';
import uniqid from 'uniqid';
import Component from './Component';
import { indexOf } from 'lodash-es';

class Components {
    constructor(app) {
        this.app = app;
        this.components = ref([]);
    }

    register(name, component) {
        this.app.component(name, component);
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

    getAppended(id) {
        return this.components.value.find((c) => c.id === id);
    }

    destroy(id) {
        let appended = this.getAppended(id);

        if (appended) {
            const index = indexOf(this.components.value, appended);
            this.components.value.splice(index, 1);
        }
    }
}

export default Components;
