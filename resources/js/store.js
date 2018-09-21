export default {

    namespaced: true,

    state: {
        windowWidth: null,
        fieldtypes: null,
    },

    mutations: {

        windowWidth(state, width) {
            state.windowWidth = width;
        },

        fieldtypes(state, fieldtypes) {
            state.fieldtypes = fieldtypes;
        }

    }

};
