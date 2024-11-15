import uid from 'uniqid';

class FieldActions {
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

    modal(props) {
        const name = props.fields ? 'field-action-modal' : 'confirmation-modal';
        return new Promise((resolve) => {
            const component = Statamic.$components.append(name, { props });
            component.on('confirm', (data = {}) => {
                if (props.keepOpen) {
                    resolve({
                        ...data,
                        confirmed: true,
                        close: () => Statamic.$components.destroy(component.id),
                    });
                } else {
                    resolve(data);
                    Statamic.$components.destroy(component.id);
                }
            });
            component.on('cancel', () => {
                resolve({
                    confirmed: false,
                });
                Statamic.$components.destroy(component.id);
            });
        });
    }
}

export default FieldActions;
