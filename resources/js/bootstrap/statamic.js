import { createApp } from 'vue';
import App from './App.vue';

export default {

    booting() {

    },

    config(config) {

    },

    start() {
        this.$app = createApp(App);

        this.$app.config.silent = false;
        this.$app.config.devtools = true;

        this.$app.config.globalProperties.$moment = window.moment;

        this.$app.mount('#statamic');
    }

}
