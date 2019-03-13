import Vue from 'vue';

export default new Vue({
    data() {
        return {
            bootingCallbacks: [],
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

        start(app) {
            this.bootingCallbacks.forEach(callback => callback(this));
            this.bootingCallbacks = [];

            new Vue(app);
        }
    }
});
