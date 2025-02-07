export default {
    namespaced: true,

    state: {
        conditions: {},
    },

    mutations: {
        condition(state, payload) {
            state.conditions[payload.name] = payload.condition;
        },
    },
};
