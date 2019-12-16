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
    },

    created() {
        this.flash = Statamic.$config.get('flash');

        this.$events.$on('toast.success', this.setFlashSuccess);
        this.$events.$on('toast.info', this.setFlashMessage);
        this.$events.$on('toast.error', this.setFlashError);
    },

    mounted() {
        this.flashExistingMessages();
    },

    methods: {

        flashExistingMessages() {
            this.flash.forEach(
                ({ type, message }) => this.setFlashMessage(message, { type: type })
            );
        },

        setFlashMessage(message, opts) {
            opts = opts || {};
            this.$toasted.show(message, opts)
        },

        setFlashSuccess(message, opts) {
            opts = opts || {};
            this.$toasted.success(message, opts)
        },

        setFlashError(message, opts) {
            opts = opts || {};
            this.$toasted.error(message, opts)
        }
    }
}
