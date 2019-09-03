class Component {

    constructor(id, name, props) {
        this.id = id;
        this.name = name;
        this.props = props;
        this.events = {};
    }

    prop(prop, value) {
        this.props[prop] = value;
    }

    on(event, handler) {
        Vue.set(this.events, event, handler);
    }

    destroy() {
        Statamic.$components.destroy(this.id);
    }

}

export default Component;
