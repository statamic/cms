export default {

    props: {
        actionUrl: String,
    },

    methods: {

        actionStarted() {
            this.loading = true;
        },

        actionCompleted() {
            this.$toast.success(__('Action completed'));
            this.request();
        }

    }

}
