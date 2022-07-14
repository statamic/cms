import Vue from 'vue';
import Echo from './Echo';
import Bard from './Bard';
import Keys from './keys/Keys';
import Hooks from './Hooks';
import Components from './Components';
import FieldConditions from './FieldConditions';
import Callbacks from './Callbacks';
const echo = new Echo;
const bard = new Bard;
const keys = new Keys;
const hooks = new Hooks;
const components = new Components;
const conditions = new FieldConditions;
const callbacks = new Callbacks;

export default new Vue({
    data() {
        return {
            bootingCallbacks: [],
            bootedCallbacks: [],
        }
    },

    computed: {

        $callbacks() {
            return callbacks;
        },

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

        $keys() {
            return keys;
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
