export default {

    data: {
        toast: null,
        flash: null,
        initialToasts: null,
    },

    created() {
        this.flash = Statamic.$config.get('flash');
        this.initialToasts = Statamic.$config.get('toasts');

        this.$events.$on('toast.success', this.setFlashSuccess);
        this.$events.$on('toast.error', this.setFlashError);
        this.$events.$on('toast.info', this.setFlashInfo);
    },

    mounted() {
        this.flashMessages(this.flash);
        this.flashMessages(this.initialToasts);
    },

    methods: {
        flashMessages(messages) {
            messages.forEach(
                ({ type, message, duration }) => {
                    const options = { duration };
                    switch(type) {
                        case 'error':
                            this.$toast.error(message, options);
                            break;
                        case 'success':
                            this.$toast.success(message, options);
                            break;
                        default:
                            this.$toast.info(message, options);
                    }
                }
            );
        },
    }
}
