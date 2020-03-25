export default {

    props: {
        actionUrl: String,
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
