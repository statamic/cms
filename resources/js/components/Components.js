import uniqid from 'uniqid';
import Component from './Component';

class Components {

    constructor(root) {
        this.$root = root;
    }

    register(name, component) {
        Vue.component(name, component);
    }

    append(name, { props }) {
        const id = `appended-${uniqid()}`;
        const component = new Component(id, name, props);
        this.$root.appendedComponents.push(component);
        return component;
    }

    get(id) {
        let appended = this.getAppended(id);
        if (appended) return appended;
    }

    getAppended(id) {
        const components = this.$root.appendedComponents;
        return _.findWhere(components, { id });
    }

    destroy(id) {
        let appended = this.getAppended(id);

        if (appended) {
            const index = _.indexOf(this.$root.appendedComponents, appended);
            this.$root.appendedComponents.splice(index, 1);
        }
    }

}

export default Components;
