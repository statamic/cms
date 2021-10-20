import Toasted from 'vue-toasted';

Vue.use(Toasted, {
    position: 'bottom-left',
    duration: 3500,
    theme: 'statamic',
    action: {
        text: '×',
        onClick: (e, toastObject) => {
            toastObject.goAway(0);
        }
    }
})

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

        setFlashInfo(message, opts) {
            opts = {
                iconPack: 'callback',
                icon: (el) => {
                    el.innerHTML = '<svg viewBox="0 0 24 24" width="24" height="24"><g transform="matrix(1,0,0,1,0,0)"><path d="M 14.25,16.5H13.5c-0.828,0-1.5-0.672-1.5-1.5v-3.75c0-0.414-0.336-0.75-0.75-0.75H10.5 " stroke="currentColor" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M 11.625,6.75 c-0.207,0-0.375,0.168-0.375,0.375S11.418,7.5,11.625,7.5S12,7.332,12,7.125S11.832,6.75,11.625,6.75L11.625,6.75 " stroke="currentColor" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M 12,0.75 c6.213,0,11.25,5.037,11.25,11.25S18.213,23.25,12,23.25S0.75,18.213,0.75,12S5.787,0.75,12,0.75z" stroke="currentColor" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></g></svg>';
                    return el;
                },
            ...opts};
            this.$toasted.show(message, this.normalizeToastOptions(opts))
        },

        setFlashSuccess(message, opts) {
            opts = {
                iconPack: 'callback',
                icon: (el) => {
                    el.innerHTML = '<svg viewBox="0 0 24 24" height="12" width="12"><g transform="matrix(1,0,0,1,0,0)"><path d="M23.146,5.4l-2.792-2.8c-0.195-0.196-0.512-0.196-0.707-0.001c0,0-0.001,0.001-0.001,0.001L7.854,14.4 c-0.195,0.196-0.512,0.196-0.707,0.001c0,0-0.001-0.001-0.001-0.001l-2.792-2.8c-0.195-0.196-0.512-0.196-0.707-0.001 c0,0-0.001,0.001-0.001,0.001l-2.792,2.8c-0.195,0.195-0.195,0.512,0,0.707L7.146,21.4c0.195,0.196,0.512,0.196,0.707,0.001 c0,0,0.001-0.001,0.001-0.001L23.146,6.1C23.337,5.906,23.337,5.594,23.146,5.4z" stroke="none" fill="currentColor" stroke-width="0" stroke-linecap="round" stroke-linejoin="round"></path></g></svg>';
                    return el;
                },
            ...opts};
            this.$toasted.success(message, this.normalizeToastOptions(opts))
        },

        setFlashError(message, opts) {
            opts = {
                iconPack: 'callback',
                icon: (el) => {
                    el.innerHTML = '<svg viewBox="0 0 24 24" height="18" width="18"><g transform="matrix(1,0,0,1,0,0)"><path d="M11.983,0C8.777,0.052,5.72,1.365,3.473,3.653C1.202,5.914-0.052,9.002,0,12.207C-0.008,18.712,5.26,23.992,11.765,24 c0.012,0,0.023,0,0.035,0h0.214c6.678-0.069,12.04-5.531,11.986-12.209l0,0c0.015-6.498-5.24-11.778-11.738-11.794 C12.169-0.003,12.076-0.002,11.983,0z M10.5,16.542c-0.03-0.815,0.606-1.499,1.421-1.529c0.009,0,0.019-0.001,0.028-0.001h0.027 c0.82,0.002,1.492,0.651,1.523,1.47c0.03,0.814-0.605,1.499-1.419,1.529c-0.01,0-0.02,0.001-0.03,0.001h-0.027 C11.203,18.009,10.532,17.361,10.5,16.542z M11,12.5v-6c0-0.552,0.448-1,1-1s1,0.448,1,1v6c0,0.552-0.448,1-1,1S11,13.052,11,12.5z" stroke="none" fill="currentColor" stroke-width="0" stroke-linecap="round" stroke-linejoin="round"></path></g></svg>';
                    return el;
                },
                ...opts
            };
            this.$toasted.error(message, this.normalizeToastOptions(opts))
        },

        normalizeToastOptions(opts) {
            if (! opts.duration) delete opts.duration;

            return opts;
        }
    }
}
