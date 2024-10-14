class Component {
    constructor(id, name, props) {
        this.id = id;
        this.name = name;
        this.props = reactive(props); // Make props reactive
        this.events = reactive({});
    }

    prop(prop, value) {
        this.props[prop] = value; // Directly set the property, Vue 3's reactivity system will track it
    }

    on(event, handler) {
        this.events[event] = handler; // Directly set the event handler
    }

    destroy() {
        // Ensure Statamic is properly imported or available in your context
        Statamic.$components.destroy(this.id);
    }
}

export default Component;
