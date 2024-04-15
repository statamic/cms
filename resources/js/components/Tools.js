class Tools {
    constructor() {
        this.tools = {};
    }

    add(name, action) {
        if (this.tools[name] === undefined) {
            this.tools[name] = [];
        }

        this.tools[name].push(action);
    }

    get(name) {
        return this.tools[name] || [];
    }
}

export default Tools;
