import axios from 'axios';

export async function requireElevatedSession() {
    const response = await axios.get(cp_url('elevated-session'));

    if (response.data.elevated) return;

    return new Promise((resolve, reject) => {
        const component = Statamic.$components.append('elevated-session-modal', {
            props: {
                method: response.data.method,
            },
        });

        component.on('closed', (shouldResolve) => {
            shouldResolve ? resolve() : reject();
            component.destroy();
        });
    });
}

export async function requireElevatedSessionIf(condition) {
    return condition ? requireElevatedSession() : Promise.resolve();
}
