export default {

    methods: {

        actionStarted() {
            this.loading = true;
        },

        actionCompleted(successful=null, response={}) {
            this.loading = false;

            if (successful === false) return;

            this.$events.$emit('clear-selections');
            this.$events.$emit('reset-action-modals');

            if (response.message !== false) {
                this.$toast.success(response.message || __("Action completed"));
            }

            this.afterActionSuccessfullyCompleted();
        },

        afterActionSuccessfullyCompleted() {
            this.request();
        }

    }

}
