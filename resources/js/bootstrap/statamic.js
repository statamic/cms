import { createApp } from 'vue';
import App from './App.vue';
import { store } from '../store/store';
import http from '../components/http';
import Config from '../components/Config';
import Preferences from '../components/Preference';
import registerGlobalComponents from './components.js';
import registerFieldtypes from './fieldtypes.js';
import useGlobalEventBus from '../composables/global-event-bus';
import useProgressBar from '../composables/progress-bar';
import VueClickAway from 'vue3-click-away';
import FloatingVue from 'floating-vue';
import 'floating-vue/dist/style.css';
import Keys from '../components/keys/Keys';
import FieldActions from "../components/field-actions/FieldActions.js";

export default {

    booting() {

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
        this.$app.use(VueClickAway);
        this.$app.use(FloatingVue, { disposeTimeout: 30000, distance: 10 });

        Object.assign(this.$app.config.globalProperties, {
            $axios: http,
            $moment: window.moment,
            $events: useGlobalEventBus(),
            $preferences: new Preferences(http, store),
            $progress: useProgressBar(),
            $config: this.$config,
            $keys: new Keys,
            $fieldActions: new FieldActions,
        });

        Object.assign(this.$app.config.globalProperties, {
            __(key, replacements) {
                return __(key, replacements);
            },
            $markdown(value) {
                return markdown(value);
            },
            can(permission) {
                const permissions = JSON.parse(atob(Statamic.$config.get('permissions')));

                return permissions.includes('super') || permissions.includes(permission);
            }
        });

        registerGlobalComponents(this.$app);
        registerFieldtypes(this.$app);

        this.$app.mount('#statamic');
    }

}
