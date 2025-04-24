export default {
    methods: {
        async requireElevatedSession() {
            const response = await this.$axios.get(cp_url('elevated-session'));

            if (response.data.elevated) return;

            return new Promise((resolve, reject) => {
                const component = Statamic.$components.append('elevated-session-modal', {
                    props: {},
                });

                component.on('closed', (shouldResolve) => {
                    shouldResolve ? resolve() : reject();
                    component.destroy();
                });
            });
        },
    },
};
