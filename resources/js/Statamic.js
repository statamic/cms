import { createApp } from 'vue';
import VueClickAway from 'vue3-click-away';
import FloatingVue from 'floating-vue'
import Toast, { POSITION } from 'vue-toastification';
import VueSelect from "vue-select";

// @todo(jack): Replace with own toast styles
// Or make a custom component instead:
// https://github.com/Maronato/vue-toastification?tab=readme-ov-file#render-a-component
import "vue-toastification/dist/index.css";

// import isLatLong from "validator/es/lib/isLatLong.js";

import { store } from './store.js';
import eventBus from './eventBus.js';
import http from './http';
import Config from './components/Config';
import Preferences from './components/Preference';
import * as GlobalComponents from './bootstrap/components.js';
import App from './components/App.vue';

import Echo from './components/Echo';
import Bard from './components/Bard';
import Keys from './components/keys/Keys';
import Hooks from './components/Hooks';
import Reveal from './components/Reveal';
import Components from './components/Components';
import FieldConditions from './components/FieldConditions';
import Callbacks from './components/Callbacks.js';
import Slugs from './components/slugs/Manager.js';
import Stacks from './components/stacks/Stacks.js';
import Portals from './components/portals/Portals.js';

import Elastic from './directives/elastic.js';
import PortalVue from 'portal-vue';
// import Toast from './mixins/Toast.js';

const echo = new Echo;
const bard = new Bard;
const keys = new Keys;
const hooks = new Hooks;
const reveal = new Reveal;
const conditions = new FieldConditions;
const callbacks = new Callbacks;
const slug = new Slugs;
const components = new Components()

export default {
    bootingCallbacks: [],
    bootedCallbacks: [],

    get $callbacks() {
        return callbacks;
    },

    get $components() {
        return components;
    },

    get $request() {
        // TODO: Custom $request error handling, etc?  For now, we'll just alias directly to $axios.
        return this.$axios;
    },

    get $echo() {
        return echo;
    },

    $bard() {
        return bard;
    },

    $hooks() {
        return hooks;
    },

    $reveal() {
        return reveal;
    },

    $conditions() {
        return conditions;
    },

    get $keys() {
        return keys;
    },

    $preferences() {
        return preferences;
    },

    $slug() {
        return slug;
    },

    component(name, component) {
        this.$app.component(name, component);
    },

    user() {
        return this.$config.get('user');
    },

    booting(callback) {
        this.bootingCallbacks.push(callback);
    },

    booted(callback) {
        this.bootedCallbacks.push(callback);
    },

    get $store() {
        return store;
    },

    get $config() {
        return new Config(store);
    },

    config(config) {
        store.commit('statamic/config', config);
    },

    start() {
        this.$app = createApp(App);
        this.$app.config.silent = false;
        this.$app.config.devtools = true;

        this.$app.use(store);
        this.$app.use(PortalVue);
        this.$app.use(VueClickAway);
        this.$app.use(FloatingVue);
        this.$app.use(Toast, {
            position: POSITION.BOTTOM_LEFT,
            timeout: 3500,
            transition: 'Vue-Toastification__slideBlurred',
        });

        this.$app.directive('elastic', Elastic);
        this.$app.directive('focus', {
            inserted: function (el) {
                el.focus();
            }
        })

        // @todo(jelleroorda): Find a replacement toaster, since this one is Vue 2 only.

        // Vue.use(VModal, { componentName: 'v-modal' })
        // Vue.use(Vuex);
        // Vue.use(VCalendar);


        this.$app.config.globalProperties.$toast = null; // Initialized in App.vue
        this.$app.config.globalProperties.$moment = window.moment;
        this.$app.config.globalProperties.$axios = http;
        this.$app.config.globalProperties.$events = eventBus;
        this.$app.config.globalProperties.$echo = this.$echo;
        this.$app.config.globalProperties.$bard = this.$bard;
        this.$app.config.globalProperties.$keys = this.$keys;
        this.$app.config.globalProperties.$reveal = this.$reveal;
        this.$app.config.globalProperties.$slug = this.$slug;
        this.$app.config.globalProperties.$preferences = new Preferences(http, store);
        this.$app.config.globalProperties.$config = this.$config;
        this.$app.config.globalProperties.$stacks = new Stacks(this.$app);
        this.$app.config.globalProperties.$portals = new Portals(this.$app);

        // Assign any global helper methods, available in all Vue components.
        Object.assign(this.$app.config.globalProperties, {
            __(key, replacements) {
                return __(key, replacements);
            },
            __n(key, number, replacements) {
                return __n(key, number, replacements);
            },
            translate(key, replacements) { // TODO: Remove
                return __(key, replacements);
            },
            $wait(ms) {
                return new Promise(resolve => {
                    setTimeout(resolve, ms);
                });
            }
        });

        // Load all global components
        Object.keys(GlobalComponents).forEach(key => {
            this.$app.component(key, GlobalComponents[key]);
        });

        this.$app.component('v-select', VueSelect);

        components.$root = this.$app;

        // Run all booting callbacks, allows addons to register their components or do whatever they need.
        this.bootingCallbacks.forEach(callback => callback(this));
        this.bootingCallbacks = [];

        // Mount app on the page.
        this.$app.mount('#statamic');

        // Run any callbacks that should run after we've mounted to the page.
        this.bootedCallbacks.forEach(callback => callback(this));
        this.bootedCallbacks = [];
    },
};
