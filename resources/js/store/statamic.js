export default {
    namespaced: true,

    state: {
        composer: {},
        config: {},
        conditions: {},
    },

    mutations: {
        composer(state, composer) {
            state.composer = composer;
        },

        config(state, config) {
            state.config = config;
        },

        configValue(state, payload) {
            state.config[payload.key] = payload.value;
        },

        condition(state, payload) {
            state.conditions[payload.name] = payload.condition;
        },
    },
};
