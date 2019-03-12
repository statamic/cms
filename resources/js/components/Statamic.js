import Vue from 'vue';

export default new Vue({
    data() {
        // State is initially set in scripts.blade.php.
        return {
            bootingCallbacks: [],
            config: {},
            flash: [],
            translations: {}
        }
    },

    computed: {
        $request() {
            // TODO: Any custom axios options we want to default to here?
            return this.axios;
        },
    },

    methods: {
        $config(key=null, defaultValue=null) {
            return key
                ? data_get(this.config, key, defaultValue)
                : this.config;
        },

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
