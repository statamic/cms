import { createStore } from 'vuex';
import statamic from './statamic.js'
// import portals from './portals.ts'

export const store = createStore({
    modules: {
        statamic,
        // portals,
        publish: {
            namespaced: true
        }
    }
});
