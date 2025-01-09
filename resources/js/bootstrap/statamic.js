import { createApp } from 'vue';
import App from './App.vue';
import { store } from '../store/store';
import Config from '../components/Config';
import * as GlobalComponents from './components.js';
import useGlobalEventBus from '../composables/global-event-bus';
import VueClickAway from 'vue3-click-away';

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

        Object.assign(this.$app.config.globalProperties, {
            $moment: window.moment,
            $events: useGlobalEventBus(),
            $config: this.$config,
        });
        
        Object.keys(GlobalComponents).forEach(key => this.$app.component(key, GlobalComponents[key]));

        this.$app.mount('#statamic');
    }

}
