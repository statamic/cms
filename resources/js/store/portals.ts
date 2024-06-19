import Portal from '../components/portals/Portal.js';

import _ from 'lodash'

export default {
    namespaced: true,

    state: {
        portals: [] as Portal[],
    },

    getters: {
        stacks(state) {
            return state.portals.filter(portal => portal.data?.type === 'stack');
        }
    },

    mutations: {
        add(state, portal: Portal) {
            state.portals.push(portal);
        },

        destroy(state, id) {
            const i = _.findIndex(state.portals, (portal: Portal) => portal.id === id);

            state.portals.splice(i, 1);
        },
    },

    actions: {
        create({ commit }, { name, data }) {
            let portal = new Portal(name, data);

            commit('add', portal)

            return portal;
        },

        createStack({ dispatch, getters }, { data }) {
            return dispatch('create', {
                name: 'stack',
                data: {
                    type: 'stack',
                    depth: getters.stacks.length + 1,
                    ...data,
                }
            })
        }
    }
}