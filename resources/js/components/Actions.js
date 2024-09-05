class Actions {
    constructor() {
        this.actions = {};
    }

    add(name, action) {
        if (this.actions[name] === undefined) {
            this.actions[name] = [];
        }

        this.actions[name].push({
            display: null,
            icon:  null,
            quick: false,
            run: () => {},
            visible: () => true,
            ...action,
        });
    }

    get(name) {
        return this.actions[name] || [];
    }
}

export default Actions;
