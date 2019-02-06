export default {

    props: {
        actions: Array,
        actionUrl: String,
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
