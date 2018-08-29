export default {

    namespaced: true,

    state: {
        windowWidth: null
    },

    mutations: {

        windowWidth(state, width) {
            state.windowWidth = width;
        }

    }

};
