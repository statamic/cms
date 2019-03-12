import Vue from 'vue';

export default new Vue({
    data() {
        return {
            config: {} // Set in scripts.blade.php.
        }
    },

    computed() {
        $request() {
            // TODO: Any custom axios options we want to default to here?
            return this.$axios;
        },

        $events() {
            return this.$events;
        },
    },

    methods: {
        $config(key=null, defaultValue=null) {
            return key
                ? data_get(this.config, key, defaultValue)
                : this.config;
        },

        $on(...args) {
            this.$events.$on(...args);
        },

        $once(...args) {
            this.$events.$once(...args);
        },

        $off(...args) {
            this.$events.$off(...args);
        },

        $emit(...args) {
            this.$events.$emit(...args);
        }
    }
});
