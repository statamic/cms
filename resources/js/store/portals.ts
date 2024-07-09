import Portal from '../components/portals/Portal.js';

import _ from 'lodash'

export default {
    namespaced: true,

    state: {
        portals: [] as Portal[],
    },

    mutations: {
        add(state, portal: Portal) {
            state.portals = [
                ...state.portals,
                portal
            ];
        },

        destroy(state, id) {
            const i = _.findIndex(state.portals, (portal: Portal) => portal.id === id);

            const portals = [...state.portals]

            portals.splice(i, 1);

            state.portals = portals
        },
    },

    actions: {
        create({ commit }, { name, data }) {
            let portal = new Portal(name, data);

            commit('add', portal)

            return portal;
        },

        createStack({ dispatch, state }, { data }) {
            // Note: we're not using the getter because that causes some weird caching to happen.
            const stacks = state.portals.filter(p => p.isStack())

            return dispatch('create', {
                name: 'stack',
                data: {
                    type: 'stack',
                    depth: stacks.length + 1,
                    ...data,
                }
            })
        }
    }
}