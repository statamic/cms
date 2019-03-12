import Vue from 'vue';

export default new Vue({
    data() {
        // State is initially set in scripts.blade.php.
        return {
            booting: [],
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

        $booting(callback) {
            this.booting.push(callback);
        },

        $start(app) {
            this.booting.forEach(callback => callback(this));
            this.booting = [];

            new Vue(app);
        }
    }
});
