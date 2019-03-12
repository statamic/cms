import Vue from 'vue';

export default new Vue({
    data() {
        return {
            config: {} // Set in scripts.blade.php.
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
        }
    }
});
