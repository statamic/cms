import uid from 'uniqid';

class Actions {
    constructor() {
        this.actions = {};
    }

    add(name, action) {
        if (this.actions[name] === undefined) {
            this.actions[name] = [];
        }

        this.actions[name].push(action);
    }

    get(name) {
        return this.actions[name] || [];
    }
}

export default Actions;
