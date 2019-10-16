import Vue from 'vue';
import Echo from './Echo';
import Bard from './Bard';
import Hooks from './Hooks';
import Components from './Components';
import FieldConditions from './FieldConditions';
const echo = new Echo;
const bard = new Bard;
const hooks = new Hooks;
const components = new Components;
const conditions = new FieldConditions;

export default new Vue({
    data() {
        return {
            bootingCallbacks: [],
            bootedCallbacks: [],
        }
    },

    computed: {

        $components() {
            return components;
        },

        $request() {
            // TODO: Custom $request error handling, etc?  For now, we'll just alias directly to $axios.
            return this.$axios;
        },

        $echo() {
            return echo;
        },

        $bard() {
            return bard;
        },

        $hooks() {
            return hooks;
        },

        $conditions() {
            return conditions;
        },

        user() {
            return this.$config.get('user');
        }

    },

    methods: {
        booting(callback) {
            this.bootingCallbacks.push(callback);
        },

        booted(callback) {
            this.bootedCallbacks.push(callback);
        },

        app(app) {
            this.$app = app;
        },

        config(config) {
            this.$store.commit('statamic/config', config);
        },

        start() {
            this.bootingCallbacks.forEach(callback => callback(this));
            this.bootingCallbacks = [];

            this.$app = new Vue(this.$app);

            this.$components.$root = this.$app;

            this.bootedCallbacks.forEach(callback => callback(this));
            this.bootedCallbacks = [];
        },

        component(name, component) {
            Vue.component(name, component);
        }
    }
});
