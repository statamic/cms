export default function(props) {
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
                resolve({...data, confirmed: true});
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
