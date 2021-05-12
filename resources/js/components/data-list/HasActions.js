export default {

    props: {
        runActionUrl: String,
        bulkActionsUrl: String,
    },

    methods: {

        actionStarted() {
            this.loading = true;
        },

        actionCompleted(successful=null) {
            this.loading = false;

            if (successful === false) return;

            this.$events.$emit('clear-selections');
            this.$events.$emit('reset-action-modals');

            this.$toast.success(__('Action completed'));

            this.request();
        }

    }

}
