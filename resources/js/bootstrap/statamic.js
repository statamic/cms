import { createApp } from 'vue';
import App from './App.vue';
import { store } from '../store/store';
import Config from '../components/Config';
import useGlobalEventBus from '../composables/global-event-bus';

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

        this.$app.config.globalProperties.$moment = window.moment;
        this.$app.config.globalProperties.$events = useGlobalEventBus();
        this.$app.config.globalProperties.$config = this.$config;

        this.$app.mount('#statamic');
    }

}
