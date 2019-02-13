import Vuex from 'vuex';
import StatamicStore from './store';

export default {
    store: new Vuex.Store({
        modules: {
            statamic: StatamicStore,
        }
    }),

    methods: {
        all() {
            return this.$store.state.statamic.preferences;
        },

        get(key, fallback) {
            return this.all()[key] || fallback;
        }
    }
};
