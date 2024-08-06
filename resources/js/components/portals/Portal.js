import uniqid from 'uniqid'
import { useStore } from 'vuex';

export default class Portal {

    constructor(name, data = {}) {
        this.id = `${name}-${uniqid()}`;
        this.data = data;
    }

    isStack() {
        return this.data?.type === 'stack'
    }

    destroy() {
        const store = useStore()

        store.commit('portals/destroy', this.id)
    }
}
