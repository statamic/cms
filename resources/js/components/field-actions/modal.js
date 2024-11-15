export default function(props) {
    const name = props.fields ? 'field-action-modal' : 'confirmation-modal';

    return new Promise((resolve) => {
        const component = Statamic.$components.append(name, { props });
        const close = () => Statamic.$components.destroy(component.id);

        component.on('confirm', (data = {}) => {
            if (props.keepOpen) {
                resolve({
                    ...data,
                    confirmed: true,
                    close,
                });
            } else {
                resolve({...data, confirmed: true});
                close();
            }
        });

        component.on('cancel', () => {
            resolve({
                confirmed: false,
            });
            close();
        });
    });
}
