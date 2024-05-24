export default {

    methods: {

        actionStarted() {
            this.loading = true;
        },

        actionCompleted(successful=null, response={}, resetUi=true) {
            this.loading = false;

            if (resetUi) {
                this.$events.$emit('clear-selections');
                this.$events.$emit('reset-action-modals');
            }

            if (successful === false) {
                if (response.message !== false) {
                    this.$toast.error(response.message || __("Action failed"));
                }

                return;
            }

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
