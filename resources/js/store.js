export default {

    namespaced: true,

    state: {
        windowWidth: null,
        fieldtypes: null,
        composer: {},
        config: {},
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

        config(state, config) {
            state.config = config;
        },

        configValue(state, payload) {
            state.config[payload.key] = payload.value;
        },

        preferences(state, preferences) {
            state.config.preferences = preferences;
        }

    }

};
