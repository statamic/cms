export default {

    namespaced: true,

    state: {
        windowWidth: null,
        fieldtypes: null,
        composer: {},
        preferences: Statamic.preferences,
        livePreview: {
            enabled: false
        }
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
        },

        livePreview(state, livePreview) {
            state.livePreview = livePreview;
        }

    }

};
