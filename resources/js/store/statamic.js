export default {
    namespaced: true,

    state: {
        composer: {},
        conditions: {},
    },

    mutations: {
        composer(state, composer) {
            state.composer = composer;
        },

        condition(state, payload) {
            state.conditions[payload.name] = payload.condition;
        },
    },
};
