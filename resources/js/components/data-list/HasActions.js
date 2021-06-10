export default {

    props: {
        actionUrl: String,
    },

    methods: {

        actionStarted() {
            this.loading = true;
        },

        actionCompleted(successful=null, response) {
            this.loading = false;

            if (successful === false) return;

            this.$events.$emit('clear-selections');
            this.$events.$emit('reset-action-modals');

            this.$toast.success(response.message || __('Action completed'));

            this.request();
        }

    }

}
