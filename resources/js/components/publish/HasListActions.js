export default {

    methods: {

        actionStarted() {
            this.saving = true;
        },

        actionCompleted(successful=null, response) {
            this.saving = false;

            if (successful === false) return;

            this.$events.$emit('reset-action-modals');

            if (response.callback) {
                Statamic.$callbacks.call(response.callback[0], ...response.callback.slice(1));
            }

            if (response.message !== false) {
                this.$toast.success(response.message || __("Action completed"));
            }

            if (response.back) {
                this.$toast.success(__("Redirecting…"));
                setTimeout(() => {
                    window.location.href = this.listingUrl;
                }, 1000);
            }

            // this.afterActionSuccessfullyCompleted();
        },

        // afterActionSuccessfullyCompleted() {
        //     this.request();
        // }

    }

}
