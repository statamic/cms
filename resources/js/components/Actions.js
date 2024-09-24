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

    async run(action, payload) {
        if (action.fieldItems) {
            payload.values = await this.modal(action);
        }
        action.run(payload);
    }

    modal(action) {
        return new Promise((resolve) => {
            const component = Statamic.$components.append('action-modal', {
                props: {
                    action
                },
            });
            component.on('submit', (values) => {
                resolve(values);
                Statamic.$components.destroy(component.id);
            });
            component.on('cancel', () => {
                resolve(false);
                Statamic.$components.destroy(component.id);
            });
        });
    }
}

export default Actions;
