export default {

    props: {
        runActionUrl: String,
        bulkActionsUrl: String,
    },

    methods: {

        actionStarted() {
            this.loading = true;
        },

        actionCompleted() {
            this.$events.$emit('clear-selections');

            this.$toast.success(__('Action completed'));

            this.request();
        }

    }

}
