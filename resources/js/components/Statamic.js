import Vue from 'vue';
import Echo from './Echo';
import Bard from './Bard';
import Keys from './keys/Keys';
import Hooks from './Hooks';
import FieldActions from './field-actions/FieldActions';
import Reveal from './Reveal';
import Components from './Components';
import FieldConditions from './FieldConditions';
import Callbacks from './Callbacks';
import Slugs from './slugs/Manager.js';
const echo = new Echo;
const bard = new Bard;
const keys = new Keys;
const hooks = new Hooks;
const fieldActions = new FieldActions;
const reveal = new Reveal;
const components = new Components;
const conditions = new FieldConditions;
const callbacks = new Callbacks;
const slug = new Slugs;

export default new Vue({
    data() {
        return {
            bootingCallbacks: [],
            bootedCallbacks: [],
            darkMode: null,
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

        $fieldActions() {
            return fieldActions;
        },

        $reveal() {
            return reveal;
        },

        $conditions() {
            return conditions;
        },

        $keys() {
            return keys;
        },

        $slug() {
            return slug;
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
