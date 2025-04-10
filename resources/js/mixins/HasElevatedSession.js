export default {
    methods: {
        async requireElevatedSession() {
            const response = await this.$axios.get(cp_url('elevated-session'));

            if (response.data.elevated) return;

            const password = await this.askForPassword();

            if (!password) throw new Error('User cancelled');

            try {
                await this.$axios.post(cp_url('elevated-session'), { password });
            } catch (error) {
                this.$toast.error(error.response.data.message);
                throw error;
            }
        },

        async askForPassword() {
            // TODO: This should be an actual modal at some point.
            return new Promise((resolve) => {
                const password = prompt('You need to enter your password to continue.');

                resolve(password);
            });
        },
    },
};
