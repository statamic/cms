import { createStore } from 'vuex';
import statamic from './statamic.js';

export const store = createStore({
    modules: {
        statamic,
        publish: {
            namespaced: true,
        },
    },
});
