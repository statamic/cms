import { createStore } from 'vuex';

import statamic from './store/statamic.js'
import portals from './store/portals.ts'

export const store = createStore({
    modules: {
        statamic,
        portals,
        publish: {
            namespaced: true
        }
    }
});
