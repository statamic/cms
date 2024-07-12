export default {

    props: {
        initialItemActions: { type: Array, default: () => [] },
        itemActionUrl: String,
    },

    data() {
        return {
            itemActions: this.initialItemActions,
        }
    },

    computed: { 

        hasItemActions() {
            return this.itemActions.length > 0;
        },

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
