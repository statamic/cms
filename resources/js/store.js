export default {

    namespaced: true,

    state: {
        windowWidth: null,
        fieldtypes: null,
        composer: {},
        preferences: Statamic.config.preferences,
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

        preferences(state, preferences) {
            state.preferences = preferences;
        }

    }

};
