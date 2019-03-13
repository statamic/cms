import Vue from 'vue';

export default new Vue({
    data() {
        return {
            bootingCallbacks: [],
            bootedCallbacks: [],
            conditions: {}, // TODO: Move to $conditions API
        }
    },

    computed: {
        $request() {
            // TODO: Any custom error handling, etc.?
            return this.axios;
        },
    },

    methods: {
        booting(callback) {
            this.bootingCallbacks.push(callback);
        },

        booted(callback) {
            this.bootedCallbacks.push(callback);
        },

        app(app) {
            this.app = app;
        },

        config(config) {
            this.$store.commit('statamic/config', config);
        },

        start() {
            this.bootingCallbacks.forEach(callback => callback(this));
            this.bootingCallbacks = [];

            this.app = new Vue(this.app);

            this.bootedCallbacks.forEach(callback => callback(this));
            this.bootedCallbacks = [];
        }
    }
});
