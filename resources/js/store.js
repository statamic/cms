export default {

    namespaced: true,

    state: {
        windowWidth: null,
        fieldtypes: null,
        composer: {},
    },

    mutations: {

        windowWidth(state, width) {
            state.windowWidth = width;
        },

        fieldtypes(state, fieldtypes) {
            state.fieldtypes = fieldtypes;
        },

        composer(state, composer) {
            state.composer = composer;
        },

    }

};
