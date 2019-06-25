export default {

    props: {
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
