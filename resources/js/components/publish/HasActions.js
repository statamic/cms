export default {

    props: {
        initialItemActions: Array,
        itemActionUrl: String,
    },

    data() {
        return {
            itemActions: this.initialItemActions,
        }
    },

    methods: {

        actionStarted() {
            this.saving = true;
        },

        actionCompleted(successful=null, response) {
            this.saving = false;

            if (successful === false) return;

            this.$events.$emit('reset-action-modals');

            if (response.message !== false) {
                this.$toast.success(response.message || __("Action completed"));
            }
            
            if (response.data) {
                this.itemActions = response.data.itemActions;
            }

            this.afterActionSuccessfullyCompleted(response);
        },

        afterActionSuccessfullyCompleted(response) {
            //
        }

    }

}
