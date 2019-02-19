export default {

    props: {
        actions: {
            type: Array,
            default: () => []
        },
        actionUrl: String,
    },

    computed: {

        hasActions() {
            return this.actions.length > 0;
        }

    },

    methods: {

        actionStarted() {
            this.loading = true;
        },

        actionCompleted() {
            this.request();
        }

    }

}
