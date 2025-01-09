import { createApp } from 'vue';
import App from './App.vue';
import { store } from '../store/store';
import http from '../components/http';
import Config from '../components/Config';
import Preferences from '../components/Preference';
import * as GlobalComponents from './components.js';
import useGlobalEventBus from '../composables/global-event-bus';
import VueClickAway from 'vue3-click-away';
import FloatingVue from 'floating-vue';
import 'floating-vue/dist/style.css';

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
            $config: this.$config,
        });
        
        Object.keys(GlobalComponents).forEach(key => this.$app.component(key, GlobalComponents[key]));

        this.$app.mount('#statamic');
    }

}
